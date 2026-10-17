<?php

return [
    'admin' => [
        'email' => env('EDEN_ADMIN_EMAIL'),
        'name' => env('EDEN_ADMIN_NAME'),
        'password' => env('EDEN_ADMIN_PASSWORD'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Blocs réservés au superadmin
    |--------------------------------------------------------------------------
    |
    | Clés de content_blocks que seul un utilisateur ayant le rôle "superadmin"
    | peut modifier. Un "editor" ne peut ni voir le crayon ni enregistrer ces
    | blocs (refus 403 côté serveur).
    |
    */
    'superadmin_only_blocks' => [
        'footer.copyright',
        'footer.credit',
    ],

    /*
    |--------------------------------------------------------------------------
    | Fuseau horaire éditorial
    |--------------------------------------------------------------------------
    |
    | Les dates sont stockées en UTC. Les dates saisies dans l'administration
    | (événements, publications, annonces) et leur affichage public utilisent
    | l'heure de Madagascar.
    |
    */
    'timezone' => env('EDEN_TIMEZONE', 'Indian/Antananarivo'),

    /*
    |--------------------------------------------------------------------------
    | File d'attente (newsletter)
    |--------------------------------------------------------------------------
    |
    | true : le planificateur (cron « schedule:run » chaque minute) traite aussi la
    | file d'attente, adapté à un hébergement mutualisé. false : un worker
    | « php artisan queue:work » tourne en continu (Supervisor, systemd).
    |
    */

    'queue_via_scheduler' => env('EDEN_QUEUE_VIA_SCHEDULER', true),

];
