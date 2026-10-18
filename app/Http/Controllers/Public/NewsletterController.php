<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscription;
use App\Services\Newsletter\SubscriptionVerification;
use App\Support\ContentLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class NewsletterController extends Controller
{
    public function store(Request $request, SubscriptionVerification $verification): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $subscription = NewsletterSubscription::subscribe($validated['email'], [
            'locale' => ContentLocale::fromRequest($request),
        ]);

        if ($subscription->trashed() || (! $subscription->wasRecentlyCreated && $subscription->status !== 'pending')) {
            throw ValidationException::withMessages(['email' => 'Cette adresse possède déjà un abonnement ou une demande. Contactez l’EDEN pour toute modification.']);
        }

        $request->session()->put('newsletter_subscription_id', $subscription->id);

        if ($subscription->wasRecentlyCreated || ! $subscription->verification_sent_at) {
            try {
                $verification->send($subscription);
            } catch (TransportExceptionInterface $exception) {
                report($exception);

                return redirect()->route('newsletter.confirmation')->with('newsletter_mail_error', true);
            }
        }

        return redirect()->route('newsletter.confirmation');
    }

    public function confirmation(Request $request): Response|RedirectResponse
    {
        $subscription = NewsletterSubscription::find($request->session()->get('newsletter_subscription_id'));

        if (! $subscription) {
            return redirect()->route('home');
        }

        return Inertia::render('NewsletterConfirmation', [
            'email' => $subscription->email,
            'status' => $subscription->status,
            'mailError' => (bool) $request->session()->get('newsletter_mail_error'),
            'resent' => (bool) $request->session()->get('newsletter_resent'),
        ]);
    }

    public function verify(Request $request, NewsletterSubscription $subscriber, string $token): RedirectResponse
    {
        abort_unless($subscriber->verification_token && hash_equals($subscriber->verification_token, $token), 404);
        $subscriber->verifyEmail($token);
        $request->session()->put('newsletter_subscription_id', $subscriber->id);

        return redirect()->route('newsletter.confirmation');
    }

    public function resend(Request $request, SubscriptionVerification $verification): RedirectResponse
    {
        $subscriber = NewsletterSubscription::findOrFail($request->session()->get('newsletter_subscription_id'));

        try {
            $verification->send($subscriber);
        } catch (TransportExceptionInterface $exception) {
            report($exception);

            return back()->with('newsletter_mail_error', true);
        }

        return back()->with('newsletter_resent', true);
    }

    /** Page de désinscription (lien présent dans chaque email). */
    public function unsubscribeForm(string $token): Response
    {
        $subscription = NewsletterSubscription::query()->where('token', $token)->firstOrFail();

        return Inertia::render('NewsletterUnsubscribe', [
            'email' => $subscription->email,
            'token' => $token,
            'unsubscribed' => $subscription->status === 'unsubscribed',
        ]);
    }

    /** Désinscription : bouton de la page ou « un clic » depuis la messagerie. */
    public function unsubscribe(Request $request, string $token): RedirectResponse|HttpResponse
    {
        $subscription = NewsletterSubscription::query()->where('token', $token)->firstOrFail();
        $subscription->unsubscribe();

        if ($request->has('List-Unsubscribe')) {
            return response('', 200);
        }

        return redirect()->route('newsletter.unsubscribe', $token);
    }
}
