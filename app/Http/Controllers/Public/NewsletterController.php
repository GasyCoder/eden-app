<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscription;
use App\Support\ContentLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class NewsletterController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $subscription = NewsletterSubscription::subscribe($validated['email'], [
            'locale' => ContentLocale::fromRequest($request),
        ]);

        return redirect()
            ->route('newsletter.confirmation')
            ->with('newsletter_email', $subscription->email);
    }

    public function confirmation(Request $request): Response|RedirectResponse
    {
        $email = $request->session()->get('newsletter_email');

        if (! $email) {
            return redirect()->route('home');
        }

        return Inertia::render('NewsletterConfirmation', [
            'email' => $email,
        ]);
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
