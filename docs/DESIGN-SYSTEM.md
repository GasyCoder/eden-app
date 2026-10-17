# EDEN, Design system et architecture du portail

Référence pour toute évolution de l'interface et des modules. À lire avant d'ajouter
une page, un composant ou un module.

---

## 1. Direction

Interface sobre et classique, d'inspiration **shadcn/ui**, aux **couleurs du logo officiel** :
vert `#13692C` (`eden-600`), rouge latérite `#AA0504` (`laterite-600`), or `#D3A20A` (`gold-500`).
Le vert porte les actions et les repères ; l'or et le rouge restent des accents (filet tricolore
`BrandStripe`, puces de rubriques, pictogrammes). Pas d'étiquettes « eyebrow », pas de gros
titres, pas de cartes surdimensionnées, pas de tiret long ou cadratin dans les textes.

- **Police unique : Inter** (variable, `@fontsource-variable/inter`).
- **Hiérarchie** : graisse et taille (titres de section `text-2xl sm:text-3xl`, h1 `text-3xl sm:text-4xl`).
- **Animations** : discrètes et utiles (héro, bandeau d'annonces, apparition des contenus),
  toutes neutralisées par `prefers-reduced-motion`.
- **Mode sombre conçu** (`[data-theme="dark"]`), il suit la préférence système par défaut.

---

## 2. Stack front

| Élément | Choix |
|---|---|
| Framework | Vue 3 + Inertia (inchangé) |
| Styles | **Tailwind CSS v4 à 100 %** (`resources/css/app.css`) + `tw-animate-css` + `@tailwindcss/typography` |
| Variantes | `class-variance-authority` (`resources/js/lib/variants.js`), fusion avec `cn()` (`clsx` + `tailwind-merge`) |
| Primitives accessibles | **Reka UI** (base de shadcn-vue) |
| Icônes | `@lucide/vue` |
| Éditeur riche | Tiptap 3, HTML nettoyé côté serveur (`RichTextSanitizer`) |

---

## 3. Tokens (`resources/css/app.css`)

- `@theme` : échelles `eden-*`, `laterite-*`, `gold-*` (50 à 950), police, animations du héro
  (`animate-hero-zoom`, `animate-hero-float`, `animate-hero-progress`).
- `:root` / `[data-theme="dark"]` : tokens sémantiques shadcn (`--background`, `--foreground`,
  `--primary`, `--muted`, `--border`, `--ring`…), exposés à Tailwind par `@theme inline`
  (`bg-background`, `text-muted-foreground`, `border-border`…).
- `@utility prose-eden` : contenu riche aux couleurs EDEN.
- Les variables `--bg-<section>` (couleurs choisies par le superadmin) restent prioritaires :
  `bg-[var(--bg-hero,var(--background))]`.

---

## 4. Composants

### Variantes (`lib/variants.js`)
`buttonVariants` (default, outline, secondary, ghost, link, destructive, destructive-ghost, inverse ;
tailles sm, default, lg, icon, icon-sm), `badgeVariants`, `inputClass`, `textareaClass`,
`cardClass`, `containerClass`.

### Composants Vue (`resources/js/Components/ui/`)
| Composant | Rôle |
|---|---|
| `Dialog` | Fenêtre accessible (focus piégé, Échap, retour du focus) |
| `ConfirmDialog` | Confirmation d'action irréversible (AlertDialog) |
| `Sheet` | Panneau latéral (menu mobile, catégories) |
| `Select` | Liste déroulante accessible, gère la valeur vide |
| `Switch` | Interrupteur avec libellé et description |
| `ActionMenu` | Menu « … » des actions d'une ligne |
| `Tooltip`, `Badge`, `EmptyState`, `Pagination`, `Field` | Utilitaires courants |

### Administration (`Components/Admin/Ui/`)
`AdminPageHeader`, `StatusBadge` (statuts harmonisés), `LocaleTabs` (FR/EN avec
pastille si traduction manquante), `RichTextEditor`, `ImageField` (téléversement,
glisser-déposer, URL), `CategoryManager`. Composables : `useListFilters` (filtres
synchronisés à l'URL, recherche différée) et `useUnsavedGuard` (alerte avant de quitter
un formulaire modifié).

---

## 5. Patterns UX

- **Formulaires** : libellé visible, « (facultatif) » plutôt qu'astérisques, aide sous le champ, erreur en clair sous le champ + alerte en tête si le formulaire est refusé. Bouton principal à droite ; état « Enregistrement… » pendant l'envoi ; toast de confirmation.
- **Contenus bilingues** : le français est obligatoire, l'anglais facultatif ; sans traduction, le site anglais affiche le français.
- **Suppression** : toujours confirmée ; proposer l'alternative non destructive (archiver, annuler).
- **Listes admin** : recherche + filtres en haut, tableau, pagination, état vide distinct de « aucun résultat ».
- **Annonces** : toutes les annonces en cours défilent dans la barre au-dessus du menu (`AnnouncementBar`, pause au survol) ; *important, urgence* ouvrent en plus une fenêtre. Une seule fenêtre par visite ; une annonce fermée n'est plus réaffichée (mémorisée localement ; « urgence » pour la session) sauf si elle est modifiée.
- **Dates** : saisies et affichées à l'heure de Madagascar (`config('eden.timezone')`), stockées en UTC.

---

## 6. Architecture des modules

| Module | Modèle | Public (FR / EN) | Admin |
|---|---|---|---|
| Actualités | `Post`, `PostCategory` | `/actualites`, `/actualites/{slug}` · `/en/news…` | `/admin/posts`, catégories dans un panneau |
| Événements | `Event` | `/evenements` (à venir / passés), `/evenements/{slug}` · `/en/events…` | `/admin/events` |
| Revues | `Journal` | `/revues`, `/revues/{slug}` · `/en/journals…` | `/admin/journals` |
| Projets (RAVENALA compris) | `Project` | `/projets`, `/projets/{slug}` · `/en/projects…` | `/admin/projects` |
| Newsletter | `NewsletterSubscription`, `NewsletterCampaign`, `NewsletterDelivery` | inscription (pied de page), `/newsletter/desabonnement/{token}` | `/admin/newsletter`, `/admin/newsletter/campaigns` |
| Paramètres du site | blocs `site.*`, `seo.*`, `contact.*`, `theme.*`, collection `footer_socials` | (balises `<head>`, en-tête, pied de page) | `/admin/settings` (superadmin) |
| Numéros (MaToY…) | `JournalIssue` | `/revues/{revue}/{numero}` · `/en/journals/{revue}/{numero}` | `/admin/journals/{id}/issues` |
| Annonces | `Announcement` | partagées par `PublicPageProps` sur toutes les pages | `/admin/announcements` |
| Pages institutionnelles | contenus éditables existants | `/a-propos`, `/formation`, `/recherche` · `/en/about…` | édition en place |

- Champs traduisibles en JSON `{fr, en}` via le trait `HasTranslations` (repli FR).
- Chaque modèle expose `toCard()`, `toDetail()`, `toAdminRow()`, `toForm()`.
- Les brouillons sont invisibles des visiteurs (404) mais prévisualisables par les administrateurs.
- `PublicPageProps::base()` fournit à chaque page publique contenus éditables, annonces et droits ; `PublicLayout` affiche en-tête, annonces, barre d'édition et pied de page.
- **Numéros de revue** (`journal_issues`) : numéro, année, période, éditorial, couverture, PDF
  (`POST /admin/uploads/documents`, 50 Mo, limité aussi par `upload_max_filesize` et `post_max_size`),
  sommaire par rubrique (JSON) et chiffres de la période (histogramme). Le dernier numéro publié
  apparaît sur l'accueil (`HomeMatoySection`). Import du MaToY N° 01 : `php artisan db:seed --class=MatoySeeder`.
- **Héro** : `/admin/hero` (textes FR/EN, boutons, diapositives `HeroSlide`). Une diapositive peut être reliée à une actualité, un événement, une annonce, un projet, un numéro de revue ou une adresse libre ; le lien n'apparaît que si le contenu est public. Dates de diffusion, cadrage mobile (`focal_point`).

### Ajouter un module
1. Migration avec champs traduisibles en `json`, `slug` unique, `status`.
2. Modèle avec `HasTranslations`, scopes de visibilité et méthodes `to*()`.
3. Contrôleur public utilisant `PublicPageProps` ; contrôleur admin utilisant `HandlesEditorialInput`.
4. Pages `Pages/<Module>/Index|Show.vue` dans `PublicLayout`, `Pages/Admin/<Module>/Index|Form.vue` dans `AdminLayout`.
5. Entrée dans la sidebar (`AdminLayout`), le menu de commandes (`CommandMenu`) et, si besoin, la navigation publique (`Navbar`).
6. Tests Pest : visibilité publique, permissions, validation.

Données de démonstration : `php artisan db:seed --class=PortalDemoSeeder` (slugs préfixés `demo-`).

## 7. Newsletter, file d'attente et tâche planifiée

- Case « Envoyer aux abonnés » dans les formulaires Actualité, Événement et Numéro de revue :
  crée une campagne (une seule par contenu). Elle part dès que le contenu est public, ou à sa
  date de publication (commande `newsletter:dispatch`, chaque minute).
- Chaque destinataire est traité par le job `SendNewsletterEmail` (lot `Bus::batch`, 3 essais),
  email `resources/views/mail/newsletter-item.blade.php` avec lien et en-tête de désinscription.
- **Production** : ajouter la tâche cron
  `* * * * * cd /chemin/vers/eden-app && php artisan schedule:run >> /dev/null 2>&1`.
  Par défaut (`EDEN_QUEUE_VIA_SCHEDULER=true`), le planificateur traite aussi la file d'attente
  (`queue:work --stop-when-empty`), adapté à un hébergement mutualisé. Avec Supervisor, passer
  la variable à `false` et lancer `php artisan queue:work` en continu.
- Configurer l'envoi réel dans `.env` : `MAIL_MAILER=smtp`, `MAIL_HOST`, `MAIL_PORT`,
  `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`.

## 8. Mobile et application installable (PWA)

- **Mobile d'abord** : barre d'onglets fixe sous 1024 px (`MobileTabBar` : Accueil, Actualités,
  Événements, Projets, Menu). La variable CSS `--tabbar-h` décale les éléments fixés en bas
  (bannières, notifications, barre d'édition, retour en haut). Zones sûres iOS via `env(safe-area-inset-*)`.
- **Manifeste** : `/manifest.webmanifest` généré par `PwaController@manifest` (nom et logo des
  Paramètres, raccourcis Actualités, Événements, Projets, Revues). Icônes `/pwa/icon-*.png`.
- **Service worker** (`public/sw.js`, production) : pages réseau d'abord puis cache (40 pages),
  build et icônes en cache, photos affichées depuis le cache puis rafraîchies (120 images),
  repli `public/offline.html`. Jamais : admin, compte, PDF, requêtes Inertia.
- **Mises à jour** : incrémenter `VERSION` dans `sw.js` à chaque changement du service worker ;
  le site propose « Mettre à jour » (`OfflineNotice`). Hors ligne, un bandeau le signale.
