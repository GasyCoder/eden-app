<?php

namespace App\Services\Newsletter;

use App\Contracts\Newsletterable;
use App\Jobs\SendNewsletterEmail;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterDelivery;
use App\Models\NewsletterSubscription;
use App\Models\User;
use Illuminate\Bus\Batch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Bus;
use Throwable;

/**
 * Programme et lance l'envoi d'un contenu aux abonnés.
 *
 * Cocher « Envoyer aux abonnés » crée une campagne « scheduled » ; elle part dès que le
 * contenu est public (immédiatement, ou à sa date de publication via le planificateur
 * `newsletter:dispatch`). Chaque destinataire est traité par un job en file d'attente.
 */
class NewsletterDispatcher
{
    /** État affiché dans les formulaires d'administration. */
    public function formState(?Model $item): array
    {
        $campaign = $item?->exists ? $this->latestCampaign($item) : null;

        return [
            'subscribers' => NewsletterSubscription::query()->active()->count(),
            'campaign' => $campaign?->toSummary(),
        ];
    }

    public function latestCampaign(Model $item): ?NewsletterCampaign
    {
        return NewsletterCampaign::query()->whereMorphedTo('notifiable', $item)->latest('id')->first();
    }

    /**
     * Demande d'envoi depuis un formulaire. Un contenu n'est envoyé qu'une fois :
     * une campagne déjà programmée, en cours ou envoyée est réutilisée.
     */
    public function request(Model&Newsletterable $item, ?User $user = null): NewsletterCampaign
    {
        $existing = NewsletterCampaign::query()->whereMorphedTo('notifiable', $item)->active()->latest('id')->first();

        if ($existing) {
            return $existing;
        }

        $campaign = NewsletterCampaign::create([
            'subject' => $item->newsletterContent('fr')['title'],
            'notifiable_type' => $item->getMorphClass(),
            'notifiable_id' => $item->getKey(),
            'status' => 'scheduled',
            'scheduled_at' => $item->newsletterReadyAt(),
            'created_by' => $user?->id,
        ]);

        $this->dispatchIfDue($campaign);

        return $campaign->refresh();
    }

    /** Lancé chaque minute par le planificateur. */
    public function dispatchDue(): int
    {
        $started = 0;

        NewsletterCampaign::query()->where('status', 'scheduled')->with('notifiable')->orderBy('id')->each(function (NewsletterCampaign $campaign) use (&$started): void {
            $started += $this->dispatchIfDue($campaign) ? 1 : 0;
        });

        return $started;
    }

    public function dispatchIfDue(NewsletterCampaign $campaign): bool
    {
        $item = $campaign->notifiable;

        if (! $item instanceof Newsletterable) {
            $campaign->forceFill(['status' => 'cancelled', 'last_error' => 'Contenu supprimé.'])->save();

            return false;
        }

        $readyAt = $item->newsletterReadyAt();
        // Suit les changements de date de publication
        $campaign->forceFill(['scheduled_at' => $readyAt, 'subject' => $item->newsletterContent('fr')['title']])->save();

        if (! $readyAt || $readyAt->isFuture()) {
            return false;
        }

        $this->start($campaign);

        return true;
    }

    public function start(NewsletterCampaign $campaign): void
    {
        $campaign->forceFill(['status' => 'queued', 'started_at' => now(), 'last_error' => null])->save();

        NewsletterSubscription::query()->active()->select('id')->chunkById(500, function ($subscriptions) use ($campaign): void {
            NewsletterDelivery::query()->insertOrIgnore($subscriptions->map(fn ($subscription) => [
                'newsletter_campaign_id' => $campaign->id,
                'newsletter_subscription_id' => $subscription->id,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ])->all());
        });

        $this->dispatchDeliveries($campaign, $campaign->deliveries()->where('status', 'pending')->pluck('id')->all());
    }

    /** Relance les destinataires en échec. */
    public function retry(NewsletterCampaign $campaign): void
    {
        $ids = $campaign->deliveries()->where('status', 'failed')->pluck('id')->all();
        NewsletterDelivery::query()->whereIn('id', $ids)->update(['status' => 'pending', 'error' => null]);
        $campaign->forceFill(['status' => 'queued', 'finished_at' => null, 'last_error' => null])->save();

        $this->dispatchDeliveries($campaign, $ids);
    }

    public function cancel(NewsletterCampaign $campaign): bool
    {
        if ($campaign->status !== 'scheduled') {
            return false;
        }

        $campaign->forceFill(['status' => 'cancelled'])->save();

        return true;
    }

    /** @param  array<int,int>  $deliveryIds */
    private function dispatchDeliveries(NewsletterCampaign $campaign, array $deliveryIds): void
    {
        $campaign->forceFill(['recipients_count' => $campaign->deliveries()->count()])->save();

        if ($deliveryIds === []) {
            $campaign->complete();

            return;
        }

        $campaignId = $campaign->id;

        try {
            $batch = Bus::batch(array_map(fn (int $id) => new SendNewsletterEmail($id), $deliveryIds))
                ->name("Newsletter #{$campaignId}")
                ->allowFailures()
                ->finally(function (Batch $batch) use ($campaignId): void {
                    NewsletterCampaign::find($campaignId)?->complete();
                })
                ->dispatch();

            // Avec une file synchrone, le lot est déjà terminé (et la campagne clôturée) ici
            $campaign->refresh()->forceFill(['batch_id' => $batch->id]);

            if ($campaign->status === 'queued') {
                $campaign->status = 'sending';
            }

            $campaign->save();
        } catch (Throwable $exception) {
            $campaign->forceFill(['status' => 'failed', 'last_error' => $exception->getMessage(), 'finished_at' => now()])->save();
        }
    }
}
