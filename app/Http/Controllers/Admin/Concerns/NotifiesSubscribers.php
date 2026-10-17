<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Contracts\Newsletterable;
use App\Services\Newsletter\NewsletterDispatcher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Case « Envoyer aux abonnés » des formulaires (actualités, événements, numéros).
 */
trait NotifiesSubscribers
{
    protected function newsletterState(?Model $item = null): array
    {
        return app(NewsletterDispatcher::class)->formState($item);
    }

    /** Programme l'envoi si la case est cochée ; renvoie un complément au message de succès. */
    protected function notifySubscribers(Request $request, Model&Newsletterable $item): string
    {
        if (! $request->boolean('notify_subscribers')) {
            return '';
        }

        $campaign = app(NewsletterDispatcher::class)->request($item, $request->user());

        return match ($campaign->status) {
            'scheduled' => ' Envoi aux abonnés programmé : il partira à la publication.',
            'sent' => ' Ce contenu a déjà été envoyé aux abonnés.',
            'failed' => ' L’envoi aux abonnés a échoué : voir Newsletter > Envois.',
            default => ' Envoi aux abonnés en cours.',
        };
    }
}
