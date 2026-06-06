<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NewsletterController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = mb_strtolower(trim($validated['email']));

        NewsletterSubscription::updateOrCreate(
            ['email' => $email],
            ['subscribed_at' => now()],
        );

        return redirect()
            ->route('newsletter.confirmation')
            ->with('newsletter_email', $email);
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
}
