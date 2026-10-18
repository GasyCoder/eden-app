<?php

use App\Models\Event;
use App\Models\User;

test('saving an event returns to its edit page despite unrelated navigation history', function (bool $wrongReferer) {
    $admin = User::factory()->create(['is_admin' => true, 'role' => 'superadmin']);
    $event = Event::factory()->create();
    $request = $this->actingAs($admin)->withSession(['_previous' => ['url' => route('admin.users.index')]]);
    if ($wrongReferer) {
        $request->withHeader('Referer', route('admin.users.index'));
    }

    $request->withHeaders(['X-Inertia' => 'true', 'X-Requested-With' => 'XMLHttpRequest'])
        ->put(route('admin.events.update', $event), [
            'title' => ['fr' => 'Titre modifié'], 'starts_at' => '2026-11-10T09:00',
            'format' => 'in_person', 'status' => 'archived',
        ])->assertStatus(303)->assertRedirect(route('admin.events.edit', $event))
        ->assertSessionHasNoErrors()->assertSessionHas('success', 'Événement enregistré.');

    expect($event->refresh()->translate('title', 'fr'))->toBe('Titre modifié')
        ->and($event->status)->toBe('archived');
})->with([false, true]);

test('event validation errors return to that event instead of the previous module', function () {
    $admin = User::factory()->create(['is_admin' => true, 'role' => 'superadmin']);
    $event = Event::factory()->create(['title' => ['fr' => 'Titre initial']]);

    $this->actingAs($admin)->withSession(['_previous' => ['url' => route('admin.users.index')]])
        ->withHeaders(['X-Inertia' => 'true', 'X-Requested-With' => 'XMLHttpRequest'])
        ->put(route('admin.events.update', $event), [
            'title' => ['fr' => ''], 'starts_at' => '2026-11-10T09:00',
            'ends_at' => '2026-11-09T09:00', 'format' => 'in_person', 'status' => 'published',
        ])->assertStatus(303)->assertRedirect(route('admin.events.edit', $event))
        ->assertSessionHasErrors(['title.fr', 'ends_at']);

    expect($event->fresh()->translate('title', 'fr'))->toBe('Titre initial');
});

test('event creation validation errors return to the event creation form', function () {
    $editor = User::factory()->create(['is_admin' => true, 'role' => 'editor']);

    $this->actingAs($editor)->withSession(['_previous' => ['url' => route('admin.users.index')]])
        ->post(route('admin.events.store'), [
            'title' => ['fr' => ''], 'starts_at' => '', 'format' => 'in_person', 'status' => 'draft',
        ])->assertRedirect(route('admin.events.create'))->assertSessionHasErrors(['title.fr', 'starts_at']);
});
