<?php

use App\Http\Controllers\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Controllers\Admin\ContentBlockController;
use App\Http\Controllers\Admin\ContentItemController;
use App\Http\Controllers\Admin\ContentRevisionController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DocumentUploadController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\HeroController;
use App\Http\Controllers\Admin\ImageUploadController;
use App\Http\Controllers\Admin\JournalController as AdminJournalController;
use App\Http\Controllers\Admin\JournalIssueController as AdminJournalIssueController;
use App\Http\Controllers\Admin\NewsletterController as AdminNewsletterController;
use App\Http\Controllers\Admin\PostCategoryController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\ProjectController as AdminProjectController;
use App\Http\Controllers\Admin\SiteSettingsController;
use App\Http\Controllers\Admin\VisitorPopupController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\EventController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\InstitutionalPageController;
use App\Http\Controllers\Public\JournalController;
use App\Http\Controllers\Public\NewsController;
use App\Http\Controllers\Public\NewsletterController;
use App\Http\Controllers\Public\ProjectController;
use App\Http\Controllers\PwaController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->defaults('locale', 'fr')->name('home');
Route::get('/fr', fn () => redirect()->route('home'))->name('home.fr');
Route::get('/en', [HomeController::class, 'index'])->defaults('locale', 'en')->name('home.en');
// Projets (RAVENALA compris) : liste et page de détail gérées dans /admin/projects
Route::get('/projets', [ProjectController::class, 'index'])->defaults('locale', 'fr')->name('projects.index');
Route::get('/en/projects', [ProjectController::class, 'index'])->defaults('locale', 'en')->name('projects.index.en');
Route::get('/projets/{project:slug}', [ProjectController::class, 'show'])->defaults('locale', 'fr')->name('projects.show');
Route::get('/en/projects/{project:slug}', [ProjectController::class, 'show'])->defaults('locale', 'en')->name('projects.show.en');
Route::get('/ravenala', fn () => redirect()->route('projects.show', 'ravenala'))->name('ravenala.short');

// Pages institutionnelles (sections éditables existantes) — FR à la racine, EN sous /en
Route::get('/a-propos', [InstitutionalPageController::class, 'about'])->defaults('locale', 'fr')->name('about');
Route::get('/en/about', [InstitutionalPageController::class, 'about'])->defaults('locale', 'en')->name('about.en');
Route::get('/formation', [InstitutionalPageController::class, 'training'])->defaults('locale', 'fr')->name('training');
Route::get('/en/training', [InstitutionalPageController::class, 'training'])->defaults('locale', 'en')->name('training.en');
Route::get('/recherche', [InstitutionalPageController::class, 'research'])->defaults('locale', 'fr')->name('research');
Route::get('/en/research', [InstitutionalPageController::class, 'research'])->defaults('locale', 'en')->name('research.en');

// Actualités
Route::get('/actualites', [NewsController::class, 'index'])->defaults('locale', 'fr')->name('news.index');
Route::get('/actualites/{post:slug}', [NewsController::class, 'show'])->defaults('locale', 'fr')->name('news.show');
Route::get('/en/news', [NewsController::class, 'index'])->defaults('locale', 'en')->name('news.index.en');
Route::get('/en/news/{post:slug}', [NewsController::class, 'show'])->defaults('locale', 'en')->name('news.show.en');

// Événements
Route::get('/evenements', [EventController::class, 'index'])->defaults('locale', 'fr')->name('events.index');
Route::get('/evenements/{event:slug}', [EventController::class, 'show'])->defaults('locale', 'fr')->name('events.show');
Route::get('/en/events', [EventController::class, 'index'])->defaults('locale', 'en')->name('events.index.en');
Route::get('/en/events/{event:slug}', [EventController::class, 'show'])->defaults('locale', 'en')->name('events.show.en');

// Revues scientifiques
Route::get('/revues', [JournalController::class, 'index'])->defaults('locale', 'fr')->name('journals.index');
Route::get('/revues/{journal:slug}', [JournalController::class, 'show'])->defaults('locale', 'fr')->name('journals.show');
Route::get('/en/journals', [JournalController::class, 'index'])->defaults('locale', 'en')->name('journals.index.en');
Route::get('/revues/{journal:slug}/{issue:slug}', [JournalController::class, 'issue'])->defaults('locale', 'fr')->scopeBindings()->name('journals.issues.show');
Route::get('/en/journals/{journal:slug}/{issue:slug}', [JournalController::class, 'issue'])->defaults('locale', 'en')->scopeBindings()->name('journals.issues.show.en');
Route::get('/en/journals/{journal:slug}', [JournalController::class, 'show'])->defaults('locale', 'en')->name('journals.show.en');
Route::post('/newsletter', [NewsletterController::class, 'store'])->middleware('throttle:5,1,newsletter-subscribe')->name('newsletter.store');
Route::get('/newsletter/confirmation', [NewsletterController::class, 'confirmation'])->name('newsletter.confirmation');
Route::get('/newsletter/verifier/{subscriber}/{token}', [NewsletterController::class, 'verify'])->where('token', '[A-Za-z0-9]{64}')->middleware(['signed', 'throttle:30,1,newsletter-verify'])->name('newsletter.verify');
Route::post('/newsletter/confirmation/renvoyer', [NewsletterController::class, 'resend'])->middleware('throttle:3,10,newsletter-resend')->name('newsletter.resend');
Route::get('/newsletter/desabonnement/{token}', [NewsletterController::class, 'unsubscribeForm'])->where('token', '[A-Za-z0-9]{20,64}')->name('newsletter.unsubscribe');
Route::post('/newsletter/desabonnement/{token}', [NewsletterController::class, 'unsubscribe'])->where('token', '[A-Za-z0-9]{20,64}')->middleware('throttle:20,1')->name('newsletter.unsubscribe.confirm');

// Application installable (PWA) : manifeste dynamique et page hors ligne
Route::get('/manifest.webmanifest', [PwaController::class, 'manifest'])->name('pwa.manifest');

// Icônes PWA générées depuis le logo/favicon téléversé (repli sur le monogramme)
Route::get('/pwa/icon-{spec}.png', [PwaController::class, 'icon'])
    ->where('spec', 'maskable-512|[0-9]{2,4}')
    ->name('pwa.icon');

Route::middleware('guest')->group(function (): void {
    // Chemin d'authentification personnalisé (les chemins par défaut /login, /register,
    // mot de passe oublié n'existent pas et renvoient donc 404).
    Route::get('/me/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/me/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:admin-login');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Espace profil de l'administrateur connecté (editor ou superadmin)
    Route::get('/me/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/me/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/me/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/me/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo');
    Route::post('/me/users', [ProfileController::class, 'storeEditor'])->name('profile.users.store');
    Route::patch('/me/users/{user}', [ProfileController::class, 'updateEditor'])->name('profile.users.update');
    Route::delete('/me/users/{user}', [ProfileController::class, 'destroyEditor'])->name('profile.users.destroy');
    Route::post('/me/onboarding/complete', [OnboardingController::class, 'complete'])->name('onboarding.complete');

    // Référencement (SEO) + favicon — superadmin uniquement (vérifié dans le contrôleur)
    Route::post('/me/settings', [SettingsController::class, 'update'])->name('settings.update');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function (): void {
    // Espace d'administration des modules éditoriaux
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::post('/posts/bulk', [AdminPostController::class, 'bulk'])->name('posts.bulk');
    Route::resource('posts', AdminPostController::class)->except('show');
    Route::resource('post-categories', PostCategoryController::class)->only(['store', 'update', 'destroy']);
    Route::post('/journals/bulk', [AdminJournalController::class, 'bulk'])->name('journals.bulk');
    Route::resource('journals', AdminJournalController::class)->except('show');
    Route::resource('journals.issues', AdminJournalIssueController::class)->except('show')->scoped();
    Route::post('/events/bulk', [AdminEventController::class, 'bulk'])->name('events.bulk');
    Route::resource('events', AdminEventController::class)->except('show');
    Route::post('/projects/bulk', [AdminProjectController::class, 'bulk'])->name('projects.bulk');
    Route::patch('/projects/reorder', [AdminProjectController::class, 'reorder'])->name('projects.reorder');
    Route::resource('projects', AdminProjectController::class)->except('show');
    Route::post('/announcements/bulk', [AdminAnnouncementController::class, 'bulk'])->name('announcements.bulk');
    Route::resource('announcements', AdminAnnouncementController::class)->except('show');

    Route::patch('/content-blocks/{key}', [ContentBlockController::class, 'update'])
        ->where('key', '[A-Za-z0-9_.-]+')
        ->name('content-blocks.update');

    Route::post('/content-items/{collectionKey}', [ContentItemController::class, 'store'])
        ->where('collectionKey', '[A-Za-z0-9_.-]+')
        ->name('content-items.store');

    Route::patch('/content-items/{contentItem}', [ContentItemController::class, 'update'])
        ->name('content-items.update');

    Route::delete('/content-items/{contentItem}', [ContentItemController::class, 'destroy'])
        ->name('content-items.destroy');

    Route::patch('/content-items/{collectionKey}/reorder', [ContentItemController::class, 'reorder'])
        ->where('collectionKey', '[A-Za-z0-9_.-]+')
        ->name('content-items.reorder');

    Route::post('/uploads/images', [ImageUploadController::class, 'store'])
        ->name('uploads.images.store');

    Route::get('/newsletter', [AdminNewsletterController::class, 'index'])->name('newsletter.index');
    Route::get('/newsletter/export', [AdminNewsletterController::class, 'export'])->name('newsletter.export');
    Route::post('/newsletter/subscribers/bulk', [AdminNewsletterController::class, 'bulk'])->name('newsletter.subscribers.bulk');
    Route::post('/newsletter/subscribers', [AdminNewsletterController::class, 'store'])->name('newsletter.subscribers.store');
    Route::patch('/newsletter/subscribers/{subscriber}', [AdminNewsletterController::class, 'update'])->name('newsletter.subscribers.update');
    Route::post('/newsletter/subscribers/{subscriber}/verification', [AdminNewsletterController::class, 'resend'])->middleware('throttle:10,1,newsletter-admin-resend')->name('newsletter.subscribers.verification');
    Route::delete('/newsletter/subscribers/{subscriber}', [AdminNewsletterController::class, 'destroy'])->name('newsletter.subscribers.destroy');
    Route::get('/newsletter/campaigns', [AdminNewsletterController::class, 'campaigns'])->name('newsletter.campaigns');
    Route::post('/newsletter/campaigns/{campaign}/cancel', [AdminNewsletterController::class, 'cancel'])->name('newsletter.campaigns.cancel');
    Route::post('/newsletter/campaigns/{campaign}/retry', [AdminNewsletterController::class, 'retry'])->name('newsletter.campaigns.retry');

    // Héro de l'accueil : textes et diaporama
    Route::get('/hero', [HeroController::class, 'index'])->name('hero.index');
    Route::put('/hero/texts', [HeroController::class, 'updateTexts'])->name('hero.texts');
    Route::patch('/hero/reorder', [HeroController::class, 'reorder'])->name('hero.reorder');
    Route::post('/hero/bulk', [HeroController::class, 'bulk'])->name('hero.bulk');
    Route::get('/hero/slides/create', [HeroController::class, 'create'])->name('hero.slides.create');
    Route::post('/hero/slides', [HeroController::class, 'store'])->name('hero.slides.store');
    Route::get('/hero/slides/{slide}/edit', [HeroController::class, 'edit'])->name('hero.slides.edit');
    Route::put('/hero/slides/{slide}', [HeroController::class, 'update'])->name('hero.slides.update');
    Route::delete('/hero/slides/{slide}', [HeroController::class, 'destroy'])->name('hero.slides.destroy');

    Route::get('/settings', [SiteSettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SiteSettingsController::class, 'update'])->name('settings.update');

    Route::post('/uploads/documents', [DocumentUploadController::class, 'store'])
        ->name('uploads.documents.store');

    Route::post('/visitor-popups', [VisitorPopupController::class, 'store'])
        ->name('visitor-popups.store');

    Route::patch('/visitor-popups/{contentItem}', [VisitorPopupController::class, 'update'])
        ->name('visitor-popups.update');

    Route::patch('/visitor-popups/{contentItem}/activate', [VisitorPopupController::class, 'activate'])
        ->name('visitor-popups.activate');

    Route::patch('/visitor-popups/{contentItem}/archive', [VisitorPopupController::class, 'archive'])
        ->name('visitor-popups.archive');

    Route::delete('/visitor-popups/{contentItem}', [VisitorPopupController::class, 'destroy'])
        ->name('visitor-popups.destroy');

    Route::get('/content-revisions', [ContentRevisionController::class, 'index'])
        ->name('content-revisions.index');

    Route::post('/content-revisions/{contentRevision}/restore', [ContentRevisionController::class, 'restore'])
        ->name('content-revisions.restore');
});

// Les méthodes non prévues ne révèlent pas non plus l'existence des routes privées.
Route::any('/admin/{path?}', fn () => abort(404))->where('path', '.*');
Route::any('/me/{path?}', fn () => abort(404))->where('path', '.*');
