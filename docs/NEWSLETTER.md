# Abonnement à la newsletter

Une adresse e-mail correspond à une seule demande. Les saisies sont normalisées (espaces retirés, minuscules) et l’index unique en base empêche les créations concurrentes en double. Une adresse déjà connue ne peut pas être réactivée par une nouvelle saisie ou un import.

## Parcours

1. Une inscription publique ou un ajout manuel crée une demande `pending` et envoie un lien signé valable 24 heures.
2. Le propriétaire confirme son adresse. La demande devient `pending_approval` : aucun envoi de newsletter n’est encore autorisé.
3. Dans le panneau newsletter, un administrateur utilise **Valider** ou **Refuser**. Il peut aussi confirmer directement une demande `pending`, sans attendre le clic du propriétaire, individuellement ou en groupe.
4. Seules les demandes `subscribed` approuvées et confirmées par le propriétaire ou par l’administration sont sélectionnées pour les campagnes. Le job vérifie à nouveau ces conditions avant chaque envoi.

Une confirmation directe est enregistrée dans `admin_confirmed_at`, avec l’identité de l’administrateur dans `approved_by`. Elle ne renseigne pas artificiellement `email_verified_at`, qui reste réservé à la confirmation par le lien e-mail. Les demandes refusées ou désinscrites ne sont pas réactivées par un simple import ou un nouveau clic de validation.

Un refus et une désinscription invalident le lien de confirmation. Un renvoi de confirmation remplace également l’ancien lien ; il est limité à un envoi par minute et à trois tentatives publiques par dix minutes. La page `/newsletter/confirmation` affiche l’état réel de la demande liée à la session.

## Envoi des e-mails

Les confirmations sont envoyées immédiatement et ne nécessitent pas de worker de campagnes. Elles utilisent le serveur de messagerie configuré pour Laravel.

Avec `MAIL_MAILER=log`, le message est enregistré dans les journaux de développement : il n’est pas livré dans la boîte mail. Pour un envoi réel, configurer `MAIL_MAILER=smtp`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_SCHEME` et `MAIL_FROM_ADDRESS` dans l’environnement, puis exécuter `php artisan config:clear`. Utiliser les valeurs fournies par le serveur SMTP ; ne pas les versionner.

`APP_URL` doit correspondre à l’adresse publique du site pour les liens générés hors requête web. Les liens signés utilisent la clé d’application.

En cas d’échec SMTP, la demande reste inactive et un nouvel essai est possible depuis la page de confirmation ou depuis l’administration. Un administrateur peut également la confirmer directement. Les doublons d’import sont ignorés sans modifier l’abonnement existant.

## Abonnements existants

La migration `2026_10_18_000005_secure_newsletter_subscriptions` conserve les adresses et leur historique, mais remet les anciens abonnements actifs en attente de confirmation : les anciennes données ne prouvent ni la propriété de l’adresse ni une validation administrative. Aucun e-mail collectif n’est envoyé par la migration. Un administrateur peut ensuite **Valider** directement ou **Renvoyer la confirmation**. La migration `2026_10_18_000006_record_newsletter_admin_confirmation` ajoute le suivi de la confirmation administrative.

## Vérification

`php artisan test` couvre le parcours, les doublons, l’expiration et l’altération des liens, la modération, les droits d’accès, les imports, les limites de renvoi, les pannes de messagerie et le contrôle des destinataires au moment de l’envoi. `npm run build` vérifie la compilation de l’interface.
