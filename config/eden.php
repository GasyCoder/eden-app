<?php

return [
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
];
