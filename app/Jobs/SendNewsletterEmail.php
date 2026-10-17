<?php

namespace App\Jobs;

use App\Contracts\Newsletterable;
use App\Mail\NewsletterItemMail;
use App\Models\NewsletterDelivery;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Throwable;

/** Envoie un contenu à un abonné (un job par destinataire, relancé en cas d'échec). */
class SendNewsletterEmail implements ShouldQueue
{
    use Batchable, Queueable;

    public int $tries = 3;

    /** @var array<int,int> */
    public array $backoff = [60, 300];

    public function __construct(public int $deliveryId) {}

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $delivery = NewsletterDelivery::query()->with(['subscription', 'campaign.notifiable'])->find($this->deliveryId);

        if (! $delivery || $delivery->status === 'sent') {
            return;
        }

        $subscription = $delivery->subscription;
        $item = $delivery->campaign?->notifiable;

        if (! $subscription || ! $subscription->isActive() || ! $item instanceof Newsletterable) {
            $delivery->update(['status' => 'skipped']);

            return;
        }

        Mail::to($subscription->email, $subscription->name)->send(new NewsletterItemMail($item, $subscription));

        $delivery->update(['status' => 'sent', 'sent_at' => now(), 'error' => null]);
    }

    public function failed(?Throwable $exception): void
    {
        $delivery = NewsletterDelivery::query()->find($this->deliveryId);
        $delivery?->update(['status' => 'failed', 'error' => mb_substr((string) $exception?->getMessage(), 0, 1000)]);
        $delivery?->campaign?->forceFill(['last_error' => mb_substr((string) $exception?->getMessage(), 0, 1000)])->save();
    }
}
