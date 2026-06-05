<?php

use App\Http\Controllers\Admin\ContentBlockController;
use App\Http\Controllers\Admin\ContentItemController;
use App\Http\Controllers\Admin\ContentRevisionController;
use App\Http\Controllers\Admin\ImageUploadController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

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

    Route::get('/content-revisions', [ContentRevisionController::class, 'index'])
        ->name('content-revisions.index');

    Route::post('/content-revisions/{contentRevision}/restore', [ContentRevisionController::class, 'restore'])
        ->name('content-revisions.restore');
});
