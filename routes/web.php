<?php

use App\Http\Controllers\Admin\ContentBlockController;
use App\Http\Controllers\Admin\ContentItemController;
use App\Http\Controllers\Admin\ContentRevisionController;
use App\Http\Controllers\Admin\ImageUploadController;
use App\Http\Controllers\Admin\VisitorPopupController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\NewsletterController;
use App\Http\Controllers\Public\RavenalaProjectController;
use App\Http\Controllers\PwaController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->defaults('locale', 'fr')->name('home');
Route::get('/fr', fn () => redirect()->route('home'))->name('home.fr');
Route::get('/en', [HomeController::class, 'index'])->defaults('locale', 'en')->name('home.en');
Route::get('/projets/ravenala', RavenalaProjectController::class)
    ->defaults('locale', 'fr')
    ->name('projects.ravenala');
Route::get('/ravenala', fn () => redirect()->route('projects.ravenala'))->name('ravenala.short');
Route::post('/newsletter', [NewsletterController::class, 'store'])->name('newsletter.store');
Route::get('/newsletter/confirmation', [NewsletterController::class, 'confirmation'])->name('newsletter.confirmation');

// Icônes PWA générées depuis le logo/favicon téléversé (repli sur le monogramme)
Route::get('/pwa/icon-{spec}.png', [PwaController::class, 'icon'])
    ->where('spec', 'maskable-512|[0-9]{2,4}')
    ->name('pwa.icon');

Route::middleware('guest')->group(function (): void {
    // Chemin d'authentification personnalisé (les chemins par défaut /login, /register,
    // mot de passe oublié n'existent pas et renvoient donc 404).
    Route::get('/me/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/me/login', [AuthenticatedSessionController::class, 'store']);
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
