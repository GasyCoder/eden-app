<?php

use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;

test('admin can rename their account using a POST with a PATCH override', function () {
    Notification::fake();
    $admin = User::factory()->create(['name' => 'Administrateur EDEN', 'is_admin' => true, 'role' => 'superadmin']);
    $password = $admin->password;
    $verifiedAt = $admin->email_verified_at;

    $this->actingAs($admin)->from('/admin/users')->post("/admin/users/{$admin->id}", [
        '_method' => 'PATCH', 'name' => 'TIC-UMG', 'email' => $admin->email, 'role' => 'admin',
    ])->assertRedirect('/admin/users')->assertSessionHasNoErrors()->assertSessionHas('success');

    expect($admin->refresh()->name)->toBe('TIC-UMG')
        ->and($admin->role)->toBe('superadmin')
        ->and($admin->password)->toBe($password)
        ->and($admin->email_verified_at->equalTo($verifiedAt))->toBeTrue();
    Notification::assertNothingSent();

    $this->get('/admin/users')->assertInertia(fn (Assert $page) => $page
        ->component('Admin/Users/Index', false)->where('users.data.0.name', 'TIC-UMG'));
});

test('admin can change another account name and role using a POST with a PATCH override', function () {
    $admin = User::factory()->create(['is_admin' => true, 'role' => 'superadmin']);
    $editor = User::factory()->create(['is_admin' => true, 'role' => 'editor']);

    $this->actingAs($admin)->from('/admin/users')->post("/admin/users/{$editor->id}", [
        '_method' => 'PATCH', 'name' => 'Nouveau nom', 'email' => $editor->email, 'role' => 'admin',
    ])->assertRedirect('/admin/users')->assertSessionHasNoErrors();

    expect($editor->refresh()->name)->toBe('Nouveau nom')->and($editor->role)->toBe('superadmin');
});

test('admin can delete another account using a POST with a DELETE override', function () {
    $admin = User::factory()->create(['is_admin' => true, 'role' => 'superadmin']);
    $editor = User::factory()->create(['is_admin' => true, 'role' => 'editor']);

    $this->actingAs($admin)->from('/admin/users')->post("/admin/users/{$editor->id}", [
        '_method' => 'DELETE',
    ])->assertRedirect('/admin/users')->assertSessionHas('success', 'Utilisateur supprimé.');

    $this->assertModelMissing($editor);
});

test('invalid edits return validation errors without changing the account', function () {
    $admin = User::factory()->create(['name' => 'Original', 'is_admin' => true, 'role' => 'superadmin']);

    $this->actingAs($admin)->from('/admin/users')->post("/admin/users/{$admin->id}", [
        '_method' => 'PATCH', 'name' => '', 'email' => $admin->email, 'role' => 'admin',
    ])->assertRedirect('/admin/users')->assertSessionHasErrors('name');

    $this->post("/admin/users/{$admin->id}", [
        '_method' => 'PATCH', 'name' => 'New', 'email' => $admin->email, 'role' => 'editor',
    ])->assertRedirect('/admin/users')->assertSessionHasErrors('role');

    expect($admin->refresh()->name)->toBe('Original')->and($admin->role)->toBe('superadmin');
});

test('editors cannot modify or delete accounts with method overrides', function () {
    $editor = User::factory()->create(['is_admin' => true, 'role' => 'editor']);
    $target = User::factory()->create(['name' => 'Original', 'is_admin' => true, 'role' => 'editor']);

    $this->actingAs($editor)->post("/admin/users/{$target->id}", [
        '_method' => 'PATCH', 'name' => 'New', 'email' => $target->email, 'role' => 'admin',
    ])->assertForbidden();

    $this->post("/admin/users/{$target->id}", ['_method' => 'DELETE'])->assertForbidden();
    expect($target->refresh()->name)->toBe('Original')->and($target->role)->toBe('editor');
});
