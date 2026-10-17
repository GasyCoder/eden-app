<?php

use App\Models\ContentBlock;
use App\Models\ContentItem;
use App\Models\ContentRevision;
use App\Models\User;
use App\Services\ContentRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

test('guest cannot update content blocks', function () {
    ContentBlock::create([
        'key' => 'hero.title',
        'value' => 'Old title',
    ]);

    $this->patch('/admin/content-blocks/hero.title', [
        'value' => 'New title',
    ])->assertNotFound();
});

test('english homepage receives locale and falls back to french content', function () {
    ContentBlock::create([
        'key' => 'hero.title',
        'locale' => 'fr',
        'value' => 'Titre français',
    ]);

    $this->get('/en')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('locale', 'en')
            ->where('blocks.hero.title', 'Titre français')
            ->where('languageUrls.fr', route('home'))
            ->where('languageUrls.en', route('home.en')));
});

test('editing a content block from english page creates english translation', function () {
    $editor = User::factory()->create(['is_admin' => true, 'role' => 'editor']);

    ContentBlock::create([
        'key' => 'hero.title',
        'locale' => 'fr',
        'value' => 'Titre français',
        'type' => 'text',
        'group' => 'hero',
    ]);

    $this->actingAs($editor)
        ->withHeader('referer', url('/en'))
        ->patch('/admin/content-blocks/hero.title', [
            'value' => 'English title',
            'type' => 'text',
        ])
        ->assertRedirect();

    expect(ContentBlock::where('key', 'hero.title')->where('locale', 'fr')->value('value'))->toBe('Titre français')
        ->and(ContentBlock::where('key', 'hero.title')->where('locale', 'en')->value('value'))->toBe('English title');
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

test('admin can update content blocks through post method spoofing', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    ContentBlock::create([
        'key' => 'site.logo',
        'type' => 'image',
        'value' => null,
    ]);

    $this->actingAs($admin)
        ->post('/admin/content-blocks/site.logo', [
            '_method' => 'PATCH',
            'value' => '/storage/content/logo.png',
            'type' => 'image',
        ])
        ->assertRedirect();

    expect(ContentBlock::where('key', 'site.logo')->value('value'))->toBe('/storage/content/logo.png');
});

test('admin can reorder repeatable content items through post method spoofing', function () {
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
        ->post('/admin/content-items/projects/reorder', [
            '_method' => 'PATCH',
            'items' => [
                ['id' => $second->id, 'position' => 0],
                ['id' => $first->id, 'position' => 1],
            ],
        ])
        ->assertRedirect();

    expect($first->refresh()->position)->toBe(1)
        ->and($second->refresh()->position)->toBe(0);
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
        ->assertNotFound();

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
        ->assertNotFound();
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
        ->assertNotFound();
});

test('editor cannot update superadmin-only footer blocks', function () {
    $editor = User::factory()->create(['is_admin' => true, 'role' => 'editor']);

    ContentBlock::create(['key' => 'footer.copyright', 'value' => '© Ancien']);

    $this->actingAs($editor)
        ->patch('/admin/content-blocks/footer.copyright', [
            'value' => '© Nouveau',
            'type' => 'text',
        ])
        ->assertForbidden();

    expect(ContentBlock::where('key', 'footer.copyright')->value('value'))->toBe('© Ancien');
});

test('superadmin can update superadmin-only footer blocks', function () {
    $superadmin = User::factory()->create(['is_admin' => true, 'role' => 'superadmin']);

    ContentBlock::create(['key' => 'footer.copyright', 'value' => '© Ancien']);

    $this->actingAs($superadmin)
        ->patch('/admin/content-blocks/footer.copyright', [
            'value' => '© Nouveau',
            'type' => 'text',
        ])
        ->assertRedirect();

    expect(ContentBlock::where('key', 'footer.copyright')->value('value'))->toBe('© Nouveau');
});

test('editor can still update non-protected blocks', function () {
    $editor = User::factory()->create(['is_admin' => true, 'role' => 'editor']);

    ContentBlock::create(['key' => 'hero.title', 'value' => 'Ancien']);

    $this->actingAs($editor)
        ->patch('/admin/content-blocks/hero.title', [
            'value' => 'Nouveau',
            'type' => 'text',
        ])
        ->assertRedirect();

    expect(ContentBlock::where('key', 'hero.title')->value('value'))->toBe('Nouveau');
});

test('editor can update text color on editable blocks', function () {
    $editor = User::factory()->create(['is_admin' => true, 'role' => 'editor']);

    ContentBlock::create(['key' => 'hero.title', 'value' => 'Ancien']);

    $this->actingAs($editor)
        ->patch('/admin/content-blocks/hero.title', [
            'value' => 'Nouveau',
            'type' => 'text',
            'meta' => ['text_color' => '#123ABC'],
        ])
        ->assertRedirect();

    expect(ContentBlock::where('key', 'hero.title')->first()->meta)
        ->toMatchArray(['text_color' => '#123ABC']);
});

test('editor can manage visitor popup settings', function () {
    $editor = User::factory()->create(['is_admin' => true, 'role' => 'editor']);

    expect(ContentBlock::where('key', 'popup.enabled')->exists())->toBeTrue()
        ->and(ContentBlock::where('key', 'popup.delay_seconds')->exists())->toBeTrue()
        ->and(ContentBlock::where('key', 'popup.image')->exists())->toBeTrue();

    $this->actingAs($editor)
        ->patch('/admin/content-blocks/popup.enabled', [
            'value' => '1',
            'type' => 'number',
        ])
        ->assertRedirect();

    $this->actingAs($editor)
        ->patch('/admin/content-blocks/popup.delay_seconds', [
            'value' => '5',
            'type' => 'number',
        ])
        ->assertRedirect();

    expect(ContentBlock::where('key', 'popup.enabled')->value('value'))->toBe('1')
        ->and(ContentBlock::where('key', 'popup.delay_seconds')->value('value'))->toBe('5');
});

test('editor can reposition visitor popup image', function () {
    $editor = User::factory()->create(['is_admin' => true, 'role' => 'editor']);

    ContentBlock::updateOrCreate(
        ['key' => 'popup.image'],
        ['type' => 'image', 'value' => '/storage/content/popup.jpg', 'group' => 'popup'],
    );

    $this->actingAs($editor)
        ->patch('/admin/content-blocks/popup.image', [
            'value' => '/storage/content/popup.jpg',
            'type' => 'image',
            'meta' => [
                'object_position_x' => 35,
                'object_position_y' => 78,
                'object_zoom' => 1.35,
            ],
        ])
        ->assertRedirect();

    expect(ContentBlock::where('key', 'popup.image')->first()->meta)
        ->toMatchArray([
            'object_position_x' => 35,
            'object_position_y' => 78,
            'object_zoom' => 1.35,
        ]);
});

test('editor can manage visitor popup list', function () {
    $editor = User::factory()->create(['is_admin' => true, 'role' => 'editor']);
    $oldPopup = ContentItem::create([
        'collection_key' => 'visitor_popups',
        'title' => 'Ancienne annonce',
        'description' => 'Ancien message',
        'link_label' => 'Voir',
        'link_url' => '#',
        'meta' => ['delay_seconds' => 3],
        'is_active' => true,
    ]);

    $this->actingAs($editor)
        ->post('/admin/visitor-popups', [
            'title' => 'Nouvelle annonce',
            'description' => '<script>alert(1)</script>Message propre',
            'link_label' => 'En savoir plus',
            'link_url' => 'https://example.com',
            'image' => '/storage/content/popup.jpg',
            'is_active' => true,
            'meta' => [
                'delay_seconds' => 5,
                'object_position_x' => 44,
                'object_position_y' => 66,
                'object_zoom' => 1.25,
            ],
        ])
        ->assertRedirect();

    $popup = ContentItem::where('collection_key', 'visitor_popups')
        ->where('title', 'Nouvelle annonce')
        ->firstOrFail();

    expect($oldPopup->refresh()->is_active)->toBeFalse()
        ->and($popup->is_active)->toBeTrue()
        ->and($popup->description)->toBe('alert(1)Message propre')
        ->and($popup->meta)->toMatchArray([
            'delay_seconds' => 5,
            'object_position_x' => 44,
            'object_position_y' => 66,
            'object_zoom' => 1.25,
        ]);

    $this->actingAs($editor)
        ->patch("/admin/visitor-popups/{$popup->id}/archive")
        ->assertRedirect();

    expect($popup->refresh()->is_active)->toBeFalse()
        ->and($popup->meta)->toHaveKey('archived_at');

    $this->actingAs($editor)
        ->patch("/admin/visitor-popups/{$popup->id}/activate")
        ->assertRedirect();

    expect($popup->refresh()->is_active)->toBeTrue()
        ->and($popup->meta)->not->toHaveKey('archived_at');

    $this->actingAs($editor)
        ->delete("/admin/visitor-popups/{$popup->id}")
        ->assertRedirect();

    expect(ContentItem::whereKey($popup->id)->exists())->toBeFalse();
});

test('visitor popup repository stays disabled when all popup items are inactive', function () {
    ContentItem::where('collection_key', 'visitor_popups')->delete();

    ContentItem::create([
        'collection_key' => 'visitor_popups',
        'title' => 'Annonce archivée',
        'description' => 'Message archivé',
        'link_label' => 'Voir',
        'link_url' => '#',
        'meta' => ['delay_seconds' => 3, 'archived_at' => now()->toISOString()],
        'is_active' => false,
    ]);

    $popup = app(ContentRepository::class)->visitorPopup([
        'enabled' => '1',
        'title' => 'Ancien fallback',
    ]);

    expect($popup['enabled'])->toBe('0')
        ->and($popup['title'])->toBe('Annonce archivée')
        ->and($popup['is_active'])->toBeFalse();
});

test('visitor popup repository is disabled when popup list is empty', function () {
    ContentItem::where('collection_key', 'visitor_popups')->delete();

    $popup = app(ContentRepository::class)->visitorPopup([
        'enabled' => '1',
        'title' => 'Ancien fallback actif',
    ]);

    expect($popup['enabled'])->toBe('0')
        ->and($popup['title'])->toBe('Ancien fallback actif')
        ->and($popup['is_active'])->toBeFalse();
});

test('invalid text color is rejected', function () {
    $editor = User::factory()->create(['is_admin' => true, 'role' => 'editor']);

    ContentBlock::create(['key' => 'hero.title', 'value' => 'Ancien']);

    $this->actingAs($editor)
        ->patch('/admin/content-blocks/hero.title', [
            'value' => 'Nouveau',
            'type' => 'text',
            'meta' => ['text_color' => 'rouge'],
        ])
        ->assertSessionHasErrors('meta.text_color');

    expect(ContentBlock::where('key', 'hero.title')->first()->meta)->toBeNull();
});

test('authenticated admin can update profile email', function () {
    $user = User::factory()->create(['is_admin' => true, 'role' => 'editor', 'email' => 'old@eden.test']);

    $this->actingAs($user)
        ->patch('/me/profile', [
            'name' => 'Nouveau Nom',
            'email' => 'new@eden.test',
        ])
        ->assertRedirect();

    expect($user->refresh()->email)->toBe('new@eden.test')
        ->and($user->name)->toBe('Nouveau Nom');
});

test('authenticated admin can change password with correct current password', function () {
    $user = User::factory()->create([
        'is_admin' => true,
        'role' => 'editor',
        'password' => 'ancienmotdepasse',
    ]);

    $this->actingAs($user)
        ->put('/me/password', [
            'current_password' => 'ancienmotdepasse',
            'password' => 'nouveaumotdepasse',
            'password_confirmation' => 'nouveaumotdepasse',
        ])
        ->assertRedirect();

    expect(Hash::check('nouveaumotdepasse', $user->refresh()->password))->toBeTrue();
});

test('guest cannot access profile', function () {
    $this->get('/me/profile')->assertNotFound();
});

test('superadmin can update seo settings', function () {
    $superadmin = User::factory()->create(['is_admin' => true, 'role' => 'superadmin']);

    $this->actingAs($superadmin)
        ->post('/me/settings', [
            'title' => 'Nouveau titre SEO',
            'description' => 'Nouvelle description',
            'keywords' => 'mot1, mot2',
        ])
        ->assertRedirect();

    expect(ContentBlock::where('key', 'seo.title')->value('value'))->toBe('Nouveau titre SEO')
        ->and(ContentBlock::where('key', 'seo.keywords')->value('value'))->toBe('mot1, mot2');
});

test('editor cannot update seo settings', function () {
    $editor = User::factory()->create(['is_admin' => true, 'role' => 'editor']);

    $this->actingAs($editor)
        ->post('/me/settings', ['title' => 'Hack'])
        ->assertForbidden();
});

test('superadmin can upload a favicon', function () {
    Storage::fake('public');
    $superadmin = User::factory()->create(['is_admin' => true, 'role' => 'superadmin']);

    $this->actingAs($superadmin)
        ->post('/me/settings', [
            'favicon' => UploadedFile::fake()->image('favicon.png', 64, 64),
        ])
        ->assertRedirect();

    expect(ContentBlock::where('key', 'site.favicon')->value('value'))->not->toBeNull();
});

test('superadmin can set a section background color', function () {
    $superadmin = User::factory()->create(['is_admin' => true, 'role' => 'superadmin']);

    $this->actingAs($superadmin)
        ->post('/me/settings', ['colors' => ['chiffres' => '#AA0000']])
        ->assertRedirect();

    expect(ContentBlock::where('key', 'theme.bg_chiffres')->value('value'))->toBe('#AA0000');
});

test('invalid section color is rejected', function () {
    $superadmin = User::factory()->create(['is_admin' => true, 'role' => 'superadmin']);

    $this->actingAs($superadmin)
        ->post('/me/settings', ['colors' => ['hero' => 'rouge']])
        ->assertSessionHasErrors('colors.hero');
});

test('editor cannot change section colors', function () {
    $editor = User::factory()->create(['is_admin' => true, 'role' => 'editor']);

    $this->actingAs($editor)
        ->post('/me/settings', ['colors' => ['hero' => '#112233']])
        ->assertForbidden();
});
