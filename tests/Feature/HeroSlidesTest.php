<?php

use App\Models\ContentBlock;
use App\Models\Event;
use App\Models\HeroSlide;
use App\Models\Post;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

function heroEditor(): User
{
    return User::factory()->create(['is_admin' => true, 'role' => 'editor']);
}

beforeEach(fn () => HeroSlide::query()->delete());

test('homepage receives live slides with links resolved to public content', function () {
    $event = Event::factory()->create(['slug' => 'colloque', 'cover_image' => '/storage/content/colloque.jpg']);
    $draft = Post::factory()->draft()->create();

    HeroSlide::create(['image' => '/storage/content/a.jpg', 'title' => ['fr' => 'Terrain'], 'link_type' => 'none', 'position' => 0]);
    HeroSlide::create(['link_type' => 'event', 'linkable_type' => Event::class, 'linkable_id' => $event->id, 'position' => 1]);
    HeroSlide::create(['image' => '/storage/content/b.jpg', 'link_type' => 'post', 'linkable_type' => Post::class, 'linkable_id' => $draft->id, 'position' => 2]);
    HeroSlide::create(['image' => '/storage/content/c.jpg', 'is_active' => false, 'position' => 3]);
    HeroSlide::create(['image' => '/storage/content/d.jpg', 'ends_at' => now()->subDay(), 'position' => 4]);

    $this->get('/')
        ->assertInertia(fn (Assert $page) => $page
            ->has('heroSlides', 3)
            ->where('heroSlides.0.link', null)
            // Sans image ni légende propres, la diapositive reprend celles de l'événement
            ->where('heroSlides.1.image', '/storage/content/colloque.jpg')
            ->where('heroSlides.1.title', $event->translate('title', 'fr'))
            ->where('heroSlides.1.link.url', route('events.show', 'colloque'))
            ->where('heroSlides.1.link.label', 'Voir l’événement')
            // Actualité non publiée : la diapositive reste, le lien est masqué
            ->where('heroSlides.2.link', null));

    $this->get('/en')->assertInertia(fn (Assert $page) => $page->where('heroSlides.1.link.label', 'See the event'));
});

test('admins create, reorder and hide slides', function () {
    $editor = heroEditor();
    $post = Post::factory()->create();

    $this->actingAs($editor)->post('/admin/hero/slides', [
        'image' => '', 'focal_point' => 'top', 'link_type' => 'post', 'linkable_id' => $post->id,
        'cta_label' => ['fr' => 'Lire'], 'is_active' => true,
    ])->assertRedirect(route('admin.hero.index'));

    $this->actingAs($editor)->post('/admin/hero/slides', [
        'image' => '/storage/content/terrain.jpg', 'focal_point' => 'center', 'link_type' => 'url', 'url' => '/formation', 'is_active' => true,
    ])->assertSessionHasNoErrors();

    [$first, $second] = HeroSlide::query()->ordered()->get();
    expect($first->linkable_id)->toBe($post->id)->and($first->link('fr')['label'])->toBe('Lire')
        ->and($second->link('fr')['url'])->toBe('/formation');

    $this->actingAs($editor)->patch('/admin/hero/reorder', ['ids' => [$second->id, $first->id]]);
    expect($second->fresh()->position)->toBe(0);

    $this->actingAs($editor)->post('/admin/hero/bulk', ['action' => 'hide', 'ids' => [$first->id]]);
    expect($first->fresh()->is_active)->toBeFalse();

    $this->actingAs($editor)->get('/admin/hero')->assertOk()->assertInertia(fn (Assert $page) => $page->component('Admin/Hero/Index', false)->has('slides', 2));
    $this->actingAs($editor)->get("/admin/hero/slides/{$first->id}/edit")->assertOk()->assertInertia(fn (Assert $page) => $page->has('linkables.post'));
});

test('slide validation requires an image without linked content and a target for links', function () {
    $editor = heroEditor();

    $this->actingAs($editor)->post('/admin/hero/slides', ['focal_point' => 'center', 'link_type' => 'none', 'image' => ''])
        ->assertSessionHasErrors('image');
    $this->actingAs($editor)->post('/admin/hero/slides', ['focal_point' => 'center', 'link_type' => 'event', 'image' => ''])
        ->assertSessionHasErrors('linkable_id');
    $this->actingAs($editor)->post('/admin/hero/slides', ['focal_point' => 'center', 'link_type' => 'url', 'image' => '/x.jpg', 'url' => 'javascript:alert(1)'])
        ->assertSessionHasErrors('url');
});

test('hero texts are saved per language from the admin', function () {
    $this->actingAs(heroEditor())->put('/admin/hero/texts', [
        'title' => ['fr' => 'Former les chercheurs', 'en' => 'Training researchers'],
        'title_highlight' => ['fr' => 'de demain'],
        'subtitle' => ['fr' => 'Introduction'],
        'button_primary_label' => ['fr' => 'Découvrir'],
        'button_secondary_label' => ['fr' => 'Candidater'],
        'button_primary_url' => '/a-propos',
        'button_secondary_url' => '/formation',
    ])->assertSessionHasNoErrors();

    expect(ContentBlock::query()->where('key', 'hero.title')->where('locale', 'en')->value('value'))->toBe('Training researchers')
        ->and(ContentBlock::query()->where('key', 'hero.button_primary_url')->where('locale', 'fr')->value('value'))->toBe('/a-propos');
});

test('the web app manifest is generated from site settings', function () {
    ContentBlock::query()->updateOrCreate(['key' => 'site.name', 'locale' => 'fr'], ['type' => 'text', 'value' => 'EDEN-UMG', 'group' => 'site', 'is_active' => true]);

    $this->get('/manifest.webmanifest')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/manifest+json')
        ->assertJsonPath('short_name', 'EDEN-UMG')
        ->assertJsonPath('theme_color', '#13692C')
        ->assertJsonCount(4, 'shortcuts');
});
