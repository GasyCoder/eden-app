<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesBulkActions;
use App\Http\Controllers\Controller;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscription;
use App\Services\Newsletter\NewsletterDispatcher;
use App\Support\ContentLocale;
use App\Support\Paginated;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Newsletter : abonnés (ajout manuel, import par liste, export CSV) et historique des envois.
 */
class NewsletterController extends Controller
{
    use HandlesBulkActions;

    public function index(Request $request): Response
    {
        $search = trim($request->string('q')->toString());
        $status = $request->string('status')->toString();

        $subscribers = $this->filtered($search, $status)->latest('id')->paginate(25);

        return Inertia::render('Admin/Newsletter/Index', [
            'subscribers' => Paginated::from($subscribers, fn (NewsletterSubscription $subscription) => $subscription->toAdminRow()),
            'filters' => ['q' => $search, 'status' => $status],
            'counts' => [
                'active' => NewsletterSubscription::query()->active()->count(),
                'unsubscribed' => NewsletterSubscription::query()->where('status', 'unsubscribed')->count(),
                'month' => NewsletterSubscription::query()->active()->where('subscribed_at', '>=', now()->subDays(30))->count(),
            ],
        ]);
    }

    /** Ajout manuel : une ou plusieurs adresses (une par ligne, ou séparées par des virgules). */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'emails' => ['required', 'string', 'max:20000'],
            'name' => ['nullable', 'string', 'max:255'],
            'locale' => ['required', Rule::in(ContentLocale::SUPPORTED)],
        ], ['emails.required' => 'Saisissez au moins une adresse.']);

        $emails = collect(preg_split('/[\s,;]+/', $data['emails']))->map(fn ($email) => mb_strtolower(trim($email)))->filter()->unique();
        $invalid = $emails->reject(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL));

        if ($invalid->isNotEmpty()) {
            throw ValidationException::withMessages(['emails' => 'Adresse(s) invalide(s) : '.$invalid->take(5)->implode(', ').($invalid->count() > 5 ? '…' : '')]);
        }

        $single = $emails->count() === 1;
        $created = 0;

        foreach ($emails as $email) {
            $existed = NewsletterSubscription::query()->where('email', $email)->exists();
            NewsletterSubscription::subscribe($email, [
                'name' => $single ? ($data['name'] ?? null) : null,
                'locale' => $data['locale'],
                ...($existed ? [] : ['source' => $single ? 'manual' : 'import']),
            ]);
            $created += $existed ? 0 : 1;
        }

        $updated = $emails->count() - $created;

        return back(303)->with('success', trim(($created ? "{$created} abonné(s) ajouté(s). " : '').($updated ? "{$updated} adresse(s) déjà connue(s), réactivée(s) si besoin." : '')));
    }

    public function update(Request $request, NewsletterSubscription $subscriber): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'locale' => ['sometimes', Rule::in(ContentLocale::SUPPORTED)],
            'status' => ['sometimes', Rule::in(NewsletterSubscription::STATUSES)],
        ]);

        if (($data['status'] ?? null) === 'unsubscribed' && $subscriber->status === 'subscribed') {
            $subscriber->unsubscribe();
        } elseif (($data['status'] ?? null) === 'subscribed' && $subscriber->status !== 'subscribed') {
            NewsletterSubscription::subscribe($subscriber->email);
        }

        $subscriber->refresh()->fill(collect($data)->only(['name', 'locale'])->all())->save();

        return back(303)->with('success', 'Abonné mis à jour.');
    }

    public function destroy(NewsletterSubscription $subscriber): RedirectResponse
    {
        $subscriber->delete();

        return back(303)->with('success', 'Abonné supprimé.');
    }

    public function export(Request $request): StreamedResponse
    {
        $ids = array_filter(array_map('intval', (array) $request->input('ids', [])));
        $rows = $this->filtered(trim($request->string('q')->toString()), $request->string('status')->toString())
            ->when($ids !== [], fn (Builder $query) => $query->whereKey($ids))
            ->orderBy('email');

        return response()->streamDownload(function () use ($rows): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM : accents corrects dans Excel
            fputcsv($out, ['email', 'nom', 'statut', 'langue', 'origine', 'inscrit le', 'désinscrit le'], ';');

            $rows->each(function (NewsletterSubscription $subscription) use ($out): void {
                fputcsv($out, [
                    $subscription->email,
                    $subscription->name,
                    $subscription->status === 'subscribed' ? 'abonné' : 'désinscrit',
                    $subscription->locale,
                    $subscription->source,
                    $subscription->subscribed_at?->timezone(config('eden.timezone'))->format('Y-m-d H:i'),
                    $subscription->unsubscribed_at?->timezone(config('eden.timezone'))->format('Y-m-d H:i'),
                ], ';');
            });

            fclose($out);
        }, 'abonnes-newsletter-eden-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function campaigns(): Response
    {
        $campaigns = NewsletterCampaign::query()->with(['notifiable', 'author'])->latest('id')->paginate(20);

        return Inertia::render('Admin/Newsletter/Campaigns', [
            'campaigns' => Paginated::from($campaigns, fn (NewsletterCampaign $campaign) => $campaign->toAdminRow()),
            'subscribers' => NewsletterSubscription::query()->active()->count(),
            'queueViaScheduler' => (bool) config('eden.queue_via_scheduler'),
            'mailer' => config('mail.default'),
            'pendingJobs' => Schema::hasTable('jobs') ? DB::table('jobs')->count() : null,
        ]);
    }

    public function cancel(NewsletterCampaign $campaign, NewsletterDispatcher $dispatcher): RedirectResponse
    {
        return $dispatcher->cancel($campaign)
            ? back(303)->with('success', 'Envoi annulé.')
            : back(303)->with('error', 'Seul un envoi programmé peut être annulé.');
    }

    public function retry(NewsletterCampaign $campaign, NewsletterDispatcher $dispatcher): RedirectResponse
    {
        if (! $campaign->deliveries()->where('status', 'failed')->exists()) {
            return back(303)->with('error', 'Aucun destinataire en échec à relancer.');
        }

        $dispatcher->retry($campaign);

        return back(303)->with('success', 'Relance programmée.');
    }

    private function filtered(string $search, string $status): Builder
    {
        return NewsletterSubscription::query()
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('email', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")))
            ->when(in_array($status, NewsletterSubscription::STATUSES, true), fn (Builder $query) => $query->where('status', $status));
    }

    protected function bulkModel(): string
    {
        return NewsletterSubscription::class;
    }

    protected function bulkActions(): array
    {
        return [
            'unsubscribe' => ['%d abonné(s) désinscrit(s).', fn (Collection $items) => $items->where('status', 'subscribed')->each->unsubscribe()],
            'resubscribe' => ['%d abonné(s) réabonné(s).', fn (Collection $items) => $items->each(fn (NewsletterSubscription $item) => NewsletterSubscription::subscribe($item->email))],
            'locale_fr' => ['%d abonné(s) passé(s) en français.', fn (Collection $items) => $items->each->update(['locale' => 'fr'])],
            'locale_en' => ['%d abonné(s) passé(s) en anglais.', fn (Collection $items) => $items->each->update(['locale' => 'en'])],
            'delete' => ['%d abonné(s) supprimé(s).', fn (Collection $items) => $items->each->delete()],
        ];
    }
}
