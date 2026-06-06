<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

test('superadmin can create an editor user from profile', function () {
    $superadmin = User::factory()->create([
        'is_admin' => true,
        'role' => 'superadmin',
    ]);

    $this->actingAs($superadmin)
        ->post('/me/users', [
            'name' => 'Éditeur EDEN',
            'email' => 'editor@example.com',
            'password' => 'motdepasse123',
            'password_confirmation' => 'motdepasse123',
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'Éditeur créé.');

    $editor = User::where('email', 'editor@example.com')->firstOrFail();

    expect($editor->name)->toBe('Éditeur EDEN')
        ->and($editor->is_admin)->toBeTrue()
        ->and($editor->role)->toBe('editor')
        ->and(Hash::check('motdepasse123', $editor->password))->toBeTrue();
});

test('editor cannot create another editor user', function () {
    $editor = User::factory()->create([
        'is_admin' => true,
        'role' => 'editor',
    ]);

    $this->actingAs($editor)
        ->post('/me/users', [
            'name' => 'Compte interdit',
            'email' => 'blocked@example.com',
            'password' => 'motdepasse123',
            'password_confirmation' => 'motdepasse123',
        ])
        ->assertForbidden();

    expect(User::where('email', 'blocked@example.com')->exists())->toBeFalse();
});

test('superadmin can update an editor user', function () {
    $superadmin = User::factory()->create([
        'is_admin' => true,
        'role' => 'superadmin',
    ]);

    $editor = User::factory()->create([
        'name' => 'Ancien Éditeur',
        'email' => 'old-editor@example.com',
        'is_admin' => true,
        'role' => 'editor',
    ]);

    $this->actingAs($superadmin)
        ->post("/me/users/{$editor->id}", [
            '_method' => 'PATCH',
            'name' => 'Éditeur Modifié',
            'email' => 'new-editor@example.com',
            'password' => 'nouveaupass123',
            'password_confirmation' => 'nouveaupass123',
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'Éditeur mis à jour.');

    $editor->refresh();

    expect($editor->name)->toBe('Éditeur Modifié')
        ->and($editor->email)->toBe('new-editor@example.com')
        ->and(Hash::check('nouveaupass123', $editor->password))->toBeTrue();
});

test('superadmin can delete an editor user', function () {
    $superadmin = User::factory()->create([
        'is_admin' => true,
        'role' => 'superadmin',
    ]);

    $editor = User::factory()->create([
        'is_admin' => true,
        'role' => 'editor',
    ]);

    $this->actingAs($superadmin)
        ->post("/me/users/{$editor->id}", [
            '_method' => 'DELETE',
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'Éditeur supprimé.');

    expect(User::find($editor->id))->toBeNull();
});

test('editor cannot update or delete editor users', function () {
    $editor = User::factory()->create([
        'is_admin' => true,
        'role' => 'editor',
    ]);

    $target = User::factory()->create([
        'name' => 'Cible',
        'email' => 'target@example.com',
        'is_admin' => true,
        'role' => 'editor',
    ]);

    $this->actingAs($editor)
        ->post("/me/users/{$target->id}", [
            '_method' => 'PATCH',
            'name' => 'Interdit',
            'email' => 'forbidden@example.com',
        ])
        ->assertForbidden();

    $this->actingAs($editor)
        ->post("/me/users/{$target->id}", [
            '_method' => 'DELETE',
        ])
        ->assertForbidden();

    expect($target->refresh()->name)->toBe('Cible')
        ->and($target->email)->toBe('target@example.com');
});

test('superadmin profile receives editor list', function () {
    $superadmin = User::factory()->create([
        'is_admin' => true,
        'role' => 'superadmin',
    ]);

    User::factory()->create([
        'name' => 'Éditeur Visible',
        'email' => 'visible@example.com',
        'is_admin' => true,
        'role' => 'editor',
    ]);

    $this->actingAs($superadmin)
        ->get('/me/profile')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('editors', 1)
            ->where('editors.0.email', 'visible@example.com'));
});

test('authenticated admin can update profile information through method spoofing', function () {
    $admin = User::factory()->create([
        'name' => 'Ancien Nom',
        'email' => 'ancien@example.com',
        'is_admin' => true,
        'role' => 'editor',
    ]);

    $this->actingAs($admin)
        ->post('/me/profile', [
            '_method' => 'PATCH',
            'name' => 'Nouveau Nom',
            'email' => 'nouveau@example.com',
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'Profil mis à jour.');

    expect($admin->refresh()->name)->toBe('Nouveau Nom')
        ->and($admin->email)->toBe('nouveau@example.com');
});

test('authenticated admin can update profile photo', function () {
    Storage::fake('public');

    $admin = User::factory()->create([
        'is_admin' => true,
        'role' => 'editor',
    ]);

    $this->actingAs($admin)
        ->post('/me/profile/photo', [
            'photo' => UploadedFile::fake()->image('profile.jpg', 320, 320),
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'Photo de profil mise à jour.');

    $path = $admin->refresh()->profile_photo_path;

    expect($path)->not->toBeNull();
    Storage::disk('public')->assertExists($path);

    $this->actingAs($admin)
        ->get('/me/profile')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('auth.user.profile_photo_url', Storage::url($path)));
});
