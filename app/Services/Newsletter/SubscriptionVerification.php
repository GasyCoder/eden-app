<?php

namespace App\Services\Newsletter;

use App\Mail\NewsletterVerificationMail;
use App\Models\NewsletterSubscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SubscriptionVerification
{
    public function send(NewsletterSubscription $subscription): void
    {
        DB::transaction(function () use ($subscription): void {
            $subscription = NewsletterSubscription::query()->lockForUpdate()->findOrFail($subscription->id);

            if ($subscription->status !== 'pending') {
                throw ValidationException::withMessages(['email' => 'Cette demande n’est pas en attente de confirmation e-mail.']);
            }

            if ($subscription->verification_sent_at?->greaterThan(now()->subMinute())) {
                throw ValidationException::withMessages(['email' => 'Veuillez patienter une minute avant de renvoyer le lien.']);
            }

            $subscription->forceFill(['verification_token' => Str::random(64)])->save();
            $url = URL::temporarySignedRoute('newsletter.verify', now()->addDay(), [
                'subscriber' => $subscription->id,
                'token' => $subscription->verification_token,
            ]);

            // Envoi immédiat : ne dépend pas du démarrage d'un worker de campagnes.
            Mail::to($subscription->email)->send(new NewsletterVerificationMail($url, $subscription->locale));
            $subscription->forceFill(['verification_sent_at' => now()])->save();
        });
    }
}
