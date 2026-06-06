<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
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
