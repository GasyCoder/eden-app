<?php

use App\Models\Announcement;
use App\Models\Event;
use App\Models\Journal;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

function portalEditor(): User
{
    return User::factory()->create(['is_admin' => true, 'role' => 'editor']);
}

function portalVisitorAccount(): User
{
    return User::factory()->create(['is_admin' => false, 'role' => 'editor']);
}

/* ─── Actualités : public ─────────────────────────────────── */

test('news index lists only published posts and highlights the featured one', function () {
    $featured = Post::factory()->create(['is_featured' => true, 'published_at' => now()->subDays(2)]);
    $published = Post::factory()->create();
    Post::factory()->draft()->create();
    Post::factory()->create(['published_at' => now()->addDay()]);

    $this->get('/actualites')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('News/Index', false)
            ->where('featured.id', $featured->id)
            ->has('posts.data', 1)
            ->where('posts.data.0.id', $published->id)
            ->where('languageUrls.en', route('news.index.en')));
});

test('news index can be filtered by category and searched', function () {
    $category = PostCategory::factory()->create(['slug' => 'soutenances']);
    $match = Post::factory()->create(['post_category_id' => $category->id, 'title' => ['fr' => 'Soutenance de thèse sur les mangroves']]);
    Post::factory()->create(['title' => ['fr' => 'Autre sujet']]);

    $this->get('/actualites?categorie=soutenances')
        ->assertInertia(fn (Assert $page) => $page->where('featured', null)->has('posts.data', 1)->where('posts.data.0.id', $match->id));

    $this->get('/actualites?q=mangroves')
        ->assertInertia(fn (Assert $page) => $page->has('posts.data', 1)->where('posts.data.0.id', $match->id));
});

test('news detail is localized with french fallback and exposes seo data', function () {
    $post = Post::factory()->create([
        'title' => ['fr' => 'Titre FR', 'en' => 'English title'],
        'body' => ['fr' => '<p>Corps français</p>'],
        'meta_description' => ['fr' => 'Description SEO'],
    ]);

    $this->get("/en/news/{$post->slug}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('News/Show', false)
            ->where('locale', 'en')
            ->where('post.title', 'English title')
            ->where('post.body', '<p>Corps français</p>')
            ->where('pageSeo.description', 'Description SEO')
            ->where('languageUrls.fr', route('news.show', $post->slug)));
});

test('draft news are hidden from visitors but previewable by admins', function () {
    $draft = Post::factory()->draft()->create();

    $this->get("/actualites/{$draft->slug}")->assertNotFound();
    $this->actingAs(portalEditor())->get("/actualites/{$draft->slug}")->assertOk();
});

/* ─── Actualités : administration ─────────────────────────── */

test('guests and non admins cannot reach the admin area', function () {
    $this->get('/admin')->assertNotFound();
    $this->get('/admin/posts')->assertNotFound();

    $this->actingAs(portalVisitorAccount())->get('/admin/posts')->assertNotFound();
    $this->actingAs(portalVisitorAccount())->post('/admin/posts', [])->assertNotFound();
});

test('admin dashboard and lists are available to editors', function () {
    Post::factory()->count(2)->create();

    $this->actingAs(portalEditor())->get('/admin')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Admin/Dashboard', false)->where('stats.posts.published', 2));

    $this->actingAs(portalEditor())->get('/admin/posts?q=zzz-introuvable')
        ->assertInertia(fn (Assert $page) => $page->component('Admin/Posts/Index', false)->has('posts.data', 0));
});

test('editor can create a post with sanitized content and generated slug', function () {
    $editor = portalEditor();

    $this->actingAs($editor)->post('/admin/posts', [
        'title' => ['fr' => 'Journée doctorale 2026', 'en' => 'Doctoral day 2026'],
        'excerpt' => ['fr' => 'Résumé'],
        'body' => ['fr' => '<p>Bonjour<script>alert(1)</script></p><a href="javascript:alert(1)">x</a>'],
        'status' => 'published',
        'is_featured' => true,
    ])->assertRedirect();

    $post = Post::firstOrFail();

    expect($post->slug)->toBe('journee-doctorale-2026')
        ->and($post->author_id)->toBe($editor->id)
        ->and($post->published_at)->not->toBeNull()
        ->and($post->translate('body', 'fr'))->not->toContain('script')
        ->and($post->translate('body', 'fr'))->not->toContain('javascript')
        ->and($post->translate('title', 'en'))->toBe('Doctoral day 2026');

    // Un second article au même titre reçoit un slug distinct
    $this->actingAs($editor)->post('/admin/posts', [
        'title' => ['fr' => 'Journée doctorale 2026'],
        'status' => 'draft',
    ]);

    expect(Post::latest('id')->first()->slug)->toBe('journee-doctorale-2026-2');
});

test('post validation requires a french title', function () {
    $this->actingAs(portalEditor())->post('/admin/posts', [
        'title' => ['en' => 'Only english'],
        'status' => 'draft',
    ])->assertSessionHasErrors('title.fr');
});

test('editor can update and delete a post', function () {
    $post = Post::factory()->create();

    $this->actingAs(portalEditor())->put("/admin/posts/{$post->id}", [
        'title' => ['fr' => 'Nouveau titre'],
        'slug' => $post->slug,
        'status' => 'draft',
    ])->assertRedirect();

    expect($post->fresh()->translate('title', 'fr'))->toBe('Nouveau titre')
        ->and($post->fresh()->status)->toBe('draft');

    $this->actingAs(portalEditor())->delete("/admin/posts/{$post->id}")->assertRedirect('/admin/posts');
    expect(Post::count())->toBe(0);
});

test('deleting a category keeps its posts', function () {
    $editor = portalEditor();
    $this->actingAs($editor)->post('/admin/post-categories', ['name' => ['fr' => 'Vie doctorale']])->assertRedirect();

    $category = PostCategory::firstOrFail();
    $post = Post::factory()->create(['post_category_id' => $category->id]);

    $this->actingAs($editor)->delete("/admin/post-categories/{$category->id}")->assertRedirect();

    expect($post->fresh()->post_category_id)->toBe($category->id)
        ->and($post->fresh()->category)->toBeNull();
    $this->assertSoftDeleted($category);
});

/* ─── Revues ──────────────────────────────────────────────── */

test('journal list hides drafts and filters by domain', function () {
    $ecology = Journal::factory()->create(['domain' => 'Écologie']);
    Journal::factory()->create(['domain' => 'Océanographie']);
    $draft = Journal::factory()->create(['status' => 'draft']);

    $this->get('/revues?domaine=Écologie')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Journals/Index', false)
            ->has('journals.data', 1)
            ->where('journals.data.0.id', $ecology->id)
            ->has('domains', 2));

    $this->get("/revues/{$draft->slug}")->assertNotFound();
});

test('journal issn is validated and normalized', function () {
    $editor = portalEditor();

    $this->actingAs($editor)->post('/admin/journals', [
        'title' => ['fr' => 'Revue test'],
        'issn' => 'abc',
        'status' => 'active',
    ])->assertSessionHasErrors('issn');

    $this->actingAs($editor)->post('/admin/journals', [
        'title' => ['fr' => 'Revue test'],
        'issn' => '1234567x',
        'status' => 'active',
    ])->assertRedirect();

    expect(Journal::firstOrFail()->issn)->toBe('1234-567X');
});

/* ─── Événements ──────────────────────────────────────────── */

test('events are split between upcoming and past', function () {
    $upcoming = Event::factory()->create();
    $past = Event::factory()->past()->create();
    Event::factory()->create(['status' => 'draft']);
    $cancelled = Event::factory()->create(['status' => 'cancelled', 'starts_at' => now()->addDays(10), 'ends_at' => null]);

    $this->get('/evenements')
        ->assertInertia(fn (Assert $page) => $page
            ->component('Events/Index', false)
            ->where('period', 'upcoming')
            ->has('events.data', 2)
            ->where('events.data.0.id', $upcoming->id)
            ->where('events.data.1.id', $cancelled->id)
            ->where('counts.past', 1));

    $this->get('/evenements?periode=passes')
        ->assertInertia(fn (Assert $page) => $page->where('period', 'past')->has('events.data', 1)->where('events.data.0.id', $past->id));
});

test('event dates are entered in Madagascar time and stored in UTC', function () {
    $this->actingAs(portalEditor())->post('/admin/events', [
        'title' => ['fr' => 'Séminaire'],
        'starts_at' => '2026-11-10T09:00',
        'ends_at' => '2026-11-10T12:00',
        'format' => 'hybrid',
        'status' => 'published',
    ])->assertRedirect();

    $event = Event::firstOrFail();

    expect($event->starts_at->utc()->format('Y-m-d H:i'))->toBe('2026-11-10 06:00')
        ->and($event->toForm()['starts_at'])->toBe('2026-11-10T09:00');
});

test('event end cannot precede its start', function () {
    $this->actingAs(portalEditor())->post('/admin/events', [
        'title' => ['fr' => 'Séminaire'],
        'starts_at' => '2026-11-10T09:00',
        'ends_at' => '2026-11-09T09:00',
        'format' => 'in_person',
        'status' => 'draft',
    ])->assertSessionHasErrors('ends_at');
});

/* ─── Annonces ────────────────────────────────────────────── */

test('only live announcements are shared with public pages', function () {
    $live = Announcement::factory()->create(['type' => 'urgent', 'priority' => 2]);
    Announcement::factory()->create(['status' => 'draft']);
    Announcement::factory()->create(['starts_at' => now()->addDay()]);
    Announcement::factory()->create(['ends_at' => now()->subMinute()]);
    $banner = Announcement::factory()->create(['type' => 'maintenance', 'priority' => 0]);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('announcements', 2)
            ->where('announcements.0.id', $live->id)
            ->where('announcements.0.display', 'dialog')
            ->where('announcements.1.id', $banner->id)
            ->where('announcements.1.display', 'banner'));
});

test('existing visitor popups are carried over as announcements', function () {
    // La migration de reprise copie la popup d'exemple créée par les migrations historiques
    expect(Announcement::query()->where('type', 'important')->exists())->toBeTrue();
});

test('announcement links must be safe', function () {
    $this->actingAs(portalEditor())->post('/admin/announcements', [
        'title' => ['fr' => 'Maintenance'],
        'cta_url' => 'javascript:alert(1)',
        'type' => 'maintenance',
        'status' => 'published',
        'priority' => 1,
    ])->assertSessionHasErrors('cta_url');

    $this->actingAs(portalEditor())->post('/admin/announcements', [
        'title' => ['fr' => 'Maintenance'],
        'cta_url' => '/evenements',
        'type' => 'maintenance',
        'status' => 'published',
        'priority' => 1,
        'starts_at' => '2026-12-01T08:00',
        'ends_at' => '2026-12-01T10:00',
    ])->assertRedirect();

    $announcement = Announcement::latest('id')->firstOrFail();

    expect($announcement->cta_url)->toBe('/evenements')
        ->and($announcement->state())->toBeIn(['scheduled', 'expired', 'live']);
});

/* ─── Pages et accueil ────────────────────────────────────── */

test('institutional pages render in both languages', function (string $url, string $component, string $locale) {
    $this->get($url)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component($component, false)->where('locale', $locale)->has('blocks'));
})->with([
    ['/a-propos', 'Institution/About', 'fr'],
    ['/en/about', 'Institution/About', 'en'],
    ['/formation', 'Institution/Training', 'fr'],
    ['/en/training', 'Institution/Training', 'en'],
    ['/recherche', 'Institution/Research', 'fr'],
    ['/en/research', 'Institution/Research', 'en'],
]);

test('homepage receives latest news, upcoming events and featured journals', function () {
    Post::factory()->count(4)->create();
    Event::factory()->count(5)->create();
    Event::factory()->past()->create();
    Journal::factory()->create(['is_featured' => true]);
    Journal::factory()->create(['is_featured' => false]);

    $this->get('/')
        ->assertInertia(fn (Assert $page) => $page
            ->has('latestPosts', 3)
            ->has('upcomingEvents', 4)
            ->has('featuredJournals', 1));
});

test('admin create and edit screens open for every module', function (string $model, string $base, string $component) {
    $record = $model::factory()->create();
    $editor = portalEditor();

    $this->actingAs($editor)->get("/admin/{$base}/create")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component($component, false));

    $this->actingAs($editor)->get("/admin/{$base}/{$record->id}/edit")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component($component, false));

    $this->actingAs($editor)->get("/admin/{$base}")->assertOk();
})->with([
    [Post::class, 'posts', 'Admin/Posts/Form'],
    [Event::class, 'events', 'Admin/Events/Form'],
    [Journal::class, 'journals', 'Admin/Journals/Form'],
    [Announcement::class, 'announcements', 'Admin/Announcements/Form'],
]);

test('non admins cannot manage any editorial module', function (string $base) {
    $user = portalVisitorAccount();

    if ($base !== 'post-categories') {
        $this->actingAs($user)->get("/admin/{$base}")->assertNotFound();
    }

    $this->actingAs($user)->post("/admin/{$base}", [])->assertNotFound();
})->with(['posts', 'events', 'journals', 'announcements', 'post-categories']);
