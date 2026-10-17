# Accès privé

La connexion se fait uniquement sur la route prévue à cet effet. Les pages et actions sous les préfixes privés renvoient la même page **404 Page introuvable** qu’une adresse inexistante lorsque la session n’appartient pas à un administrateur. Il n’y a aucune redirection vers la connexion, y compris pour les requêtes JSON, les méthodes non prévues et les identifiants de ressources inexistants. La page 404 ne contient ni lien de connexion, ni trace technique.

Le contrôle intervient après le chargement de la session, avant l’authentification qui provoquerait une redirection, la résolution des modèles et le contrôle CSRF. La connexion conserve sa protection CSRF. Les permissions supplémentaires des éditeurs et superadministrateurs restent vérifiées par les contrôleurs.

Seuls les comptes administrateurs peuvent se connecter. Les identifiants incorrects, comptes inconnus et comptes non administrateurs reçoivent le même message. Les tentatives sont limitées à cinq par minute pour une adresse e-mail et une IP, et à vingt par minute pour une IP indépendamment de l’adresse. Modifier la casse de l’adresse ne contourne pas la limite. La session est renouvelée à la connexion et invalidée à la déconnexion.

Les réponses privées sont marquées `no-store`, `noindex`, `nofollow`, `noarchive` et n’envoient pas de référent. Les pages publiques rendues pour une session connectée ne sont pas mises en cache non plus. Le service worker ignore les réponses `no-store` et sa nouvelle version évacue les anciens caches. La page de connexion ne présente pas de lien canonique ou de lien de langue et son composant JavaScript ne contient pas de chemin de connexion codé en dur.

Une URL publique reste découvrable : masquer les redirections et ne pas l’indexer ne constitue pas une barrière réseau contre les scanners, les robots ou l’IA. Aucun contrôle de sécurité ne repose sur le `User-Agent`. Une restriction réseau, par exemple un VPN ou une liste d’IP autorisées, serait nécessaire pour rendre la page inaccessible depuis Internet.

En production, utiliser HTTPS et `APP_DEBUG=false`. Avec HTTPS, activer également `SESSION_SECURE_COOKIE=true`. Ces réglages restent propres à l’environnement et ne doivent pas empêcher les connexions locales de développement en HTTP.

`PrivateAccessTest` vérifie la 404, l’absence de redirection, les méthodes HTTP, les réponses JSON, la protection CSRF, les permissions, les limites de connexion, le renouvellement de session et les en-têtes privés.
