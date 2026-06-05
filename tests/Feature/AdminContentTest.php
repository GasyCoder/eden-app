<?php

use App\Models\ContentBlock;
use App\Models\ContentItem;
use App\Models\ContentRevision;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('guest cannot update content blocks', function () {
    ContentBlock::create([
        'key' => 'hero.title',
        'value' => 'Old title',
    ]);

    $this->patch('/admin/content-blocks/hero.title', [
        'value' => 'New title',
    ])->assertRedirect('/me/login');
});

test('admin can update content blocks', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    ContentBlock::create([
        'key' => 'hero.title',
        'value' => 'Old title',
    ]);

    $this->actingAs($admin)
        ->patch('/admin/content-blocks/hero.title', [
            'value' => 'New title',
            'type' => 'text',
        ])
        ->assertRedirect();

    expect(ContentBlock::where('key', 'hero.title')->value('value'))->toBe('New title');
    expect(ContentRevision::where('content_type', 'block')
        ->where('content_key', 'hero.title')
        ->where('action', 'updated')
        ->exists())->toBeTrue();
});

test('non admin cannot update content blocks', function () {
    $user = User::factory()->create(['is_admin' => false]);

    ContentBlock::create([
        'key' => 'hero.title',
        'value' => 'Old title',
    ]);

    $this->actingAs($user)
        ->patch('/admin/content-blocks/hero.title', [
            'value' => 'New title',
            'type' => 'text',
        ])
        ->assertForbidden();

    expect(ContentBlock::where('key', 'hero.title')->value('value'))->toBe('Old title');
});

test('admin can manage repeatable content items', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->post('/admin/content-items/projects', [
            'title' => 'Projet test',
            'subtitle' => 'Axe test',
            'description' => 'Description test',
            'link_url' => '#projet',
            'link_label' => 'Voir',
            'meta' => ['status' => 'En cours'],
        ])
        ->assertRedirect();

    $item = ContentItem::where('collection_key', 'projects')->firstOrFail();

    $this->actingAs($admin)
        ->patch("/admin/content-items/{$item->id}", [
            'title' => 'Projet modifie',
            'description' => '<script>alert(1)</script>Description propre',
        ])
        ->assertRedirect();

    $item->refresh();

    expect($item->title)->toBe('Projet modifie')
        ->and($item->description)->toBe('alert(1)Description propre');

    expect(ContentRevision::where('content_type', 'item')
        ->where('content_key', 'projects')
        ->where('action', 'updated')
        ->exists())->toBeTrue();
});

test('admin can reorder repeatable content items', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $first = ContentItem::create([
        'collection_key' => 'projects',
        'title' => 'Premier',
        'position' => 0,
    ]);
    $second = ContentItem::create([
        'collection_key' => 'projects',
        'title' => 'Second',
        'position' => 1,
    ]);

    $this->actingAs($admin)
        ->patch('/admin/content-items/projects/reorder', [
            'items' => [
                ['id' => $second->id, 'position' => 0],
                ['id' => $first->id, 'position' => 1],
            ],
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'Ordre enregistré.');

    expect($first->refresh()->position)->toBe(1)
        ->and($second->refresh()->position)->toBe(0);

    expect(ContentRevision::where('content_type', 'collection')
        ->where('content_key', 'projects')
        ->where('action', 'reordered')
        ->exists())->toBeTrue();
});

test('admin can upload content images', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['is_admin' => true]);

    $response = $this->actingAs($admin)
        ->postJson('/admin/uploads/images', [
            'image' => UploadedFile::fake()->image('hero.jpg', 1200, 800),
        ])
        ->assertOk()
        ->assertJsonStructure(['path']);

    $path = str_replace('/storage/', '', $response->json('path'));

    Storage::disk('public')->assertExists($path);
});

test('non admin cannot upload content images', function () {
    Storage::fake('public');

    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->postJson('/admin/uploads/images', [
            'image' => UploadedFile::fake()->image('hero.jpg'),
        ])
        ->assertForbidden();
});

test('admin can list content revisions', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    ContentRevision::create([
        'user_id' => $admin->id,
        'content_type' => 'block',
        'content_key' => 'hero.title',
        'action' => 'updated',
        'before' => ['value' => 'Ancien'],
        'after' => ['value' => 'Nouveau'],
    ]);

    $this->actingAs($admin)
        ->getJson('/admin/content-revisions')
        ->assertOk()
        ->assertJsonPath('revisions.0.content_key', 'hero.title')
        ->assertJsonPath('revisions.0.can_restore', true)
        ->assertJsonPath('revisions.0.changes.0.field', 'Valeur')
        ->assertJsonPath('revisions.0.changes.0.before', 'Ancien')
        ->assertJsonPath('revisions.0.changes.0.after', 'Nouveau');
});

test('admin can restore a previous block revision', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    ContentBlock::create([
        'key' => 'hero.title',
        'type' => 'text',
        'value' => 'Ancien titre',
    ]);

    $this->actingAs($admin)
        ->patch('/admin/content-blocks/hero.title', [
            'value' => 'Nouveau titre',
            'type' => 'text',
        ])
        ->assertRedirect();

    $revision = ContentRevision::where('content_type', 'block')
        ->where('content_key', 'hero.title')
        ->where('action', 'updated')
        ->firstOrFail();

    $this->actingAs($admin)
        ->post("/admin/content-revisions/{$revision->id}/restore")
        ->assertRedirect()
        ->assertSessionHas('success', 'Version restaurée.');

    expect(ContentBlock::where('key', 'hero.title')->value('value'))->toBe('Ancien titre');
});

test('admin can restore a deleted content item', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $item = ContentItem::create([
        'collection_key' => 'projects',
        'title' => 'Projet à restaurer',
        'position' => 0,
    ]);

    $this->actingAs($admin)
        ->delete("/admin/content-items/{$item->id}")
        ->assertRedirect();

    expect(ContentItem::find($item->id))->toBeNull();

    $revision = ContentRevision::where('content_type', 'item')
        ->where('content_id', $item->id)
        ->where('action', 'deleted')
        ->firstOrFail();

    $this->actingAs($admin)
        ->post("/admin/content-revisions/{$revision->id}/restore")
        ->assertRedirect();

    expect(ContentItem::find($item->id)?->title)->toBe('Projet à restaurer');
});

test('non admin cannot list content revisions', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->getJson('/admin/content-revisions')
        ->assertForbidden();
});
