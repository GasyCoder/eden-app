<?php

use App\Models\User;

test('admin can complete onboarding guide', function () {
    $admin = User::factory()->create([
        'is_admin' => true,
        'role' => 'editor',
        'onboarding_completed_at' => null,
    ]);

    $this->actingAs($admin)
        ->post('/me/onboarding/complete')
        ->assertRedirect()
        ->assertSessionHas('success', 'Guide terminé.');

    expect($admin->refresh()->onboarding_completed_at)->not->toBeNull();
});

test('non admin cannot complete onboarding guide', function () {
    $user = User::factory()->create([
        'is_admin' => false,
        'onboarding_completed_at' => null,
    ]);

    $this->actingAs($user)
        ->post('/me/onboarding/complete')
        ->assertForbidden();

    expect($user->refresh()->onboarding_completed_at)->toBeNull();
});
