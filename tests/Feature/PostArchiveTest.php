<?php

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('an editor can create a publicly consultable archived post without sending a newsletter', function () {
    $editor = User::factory()->create(['is_admin' => true, 'role' => 'editor']);

    $this->actingAs($editor)->post('/admin/posts', [
        'title' => ['fr' => 'Archive institutionnelle'], 'status' => 'archived', 'notify_subscribers' => true,
    ])->assertSessionHasNoErrors()->assertSessionHas('success', 'Actualité archivée.');

    $post = Post::firstOrFail();
    expect($post->status)->toBe('archived')->and($post->author_id)->toBe($editor->id)
        ->and($post->isPublic())->toBeTrue()->and($post->newsletterReadyAt())->toBeNull();
    $this->assertDatabaseCount('newsletter_campaigns', 0);
});

test('archiving preserves the article and publication date and returns to its edit form', function () {
    $editor = User::factory()->create(['is_admin' => true, 'role' => 'editor']);
    $post = Post::factory()->create();
    $publishedAt = $post->published_at;
    $body = $post->translate('body', 'fr');
    $payload = [...$post->toForm(), 'status' => 'archived', 'notify_subscribers' => true];
    unset($payload['published_at']);

    $this->actingAs($editor)->withSession(['_previous' => ['url' => route('admin.users.index')]])
        ->put("/admin/posts/{$post->id}", $payload)
        ->assertRedirect(route('admin.posts.edit', $post))->assertSessionHasNoErrors()
        ->assertSessionHas('success', 'Actualité archivée.');

    expect($post->refresh()->status)->toBe('archived')
        ->and($post->published_at->equalTo($publishedAt))->toBeTrue()
        ->and($post->translate('body', 'fr'))->toBe($body);
    $this->assertDatabaseCount('posts', 1);
    $this->assertDatabaseCount('newsletter_campaigns', 0);
});

test('archived posts remain public with their status but are excluded from the homepage and featured news', function () {
    $archived = Post::factory()->create(['status' => 'archived', 'is_featured' => true, 'published_at' => now()->subDays(3)]);
    $published = Post::factory()->create();

    $this->get('/actualites')->assertInertia(fn (Assert $page) => $page
        ->where('featured', null)->has('posts.data', 2)->where('posts.data.0.id', $published->id)
        ->where('posts.data.1.id', $archived->id)->where('posts.data.1.status', 'archived'));
    $this->get('/')->assertInertia(fn (Assert $page) => $page
        ->has('latestPosts', 1)->where('latestPosts.0.id', $published->id));
    foreach (['news.show', 'news.show.en'] as $route) {
        $this->get(route($route, $archived->slug))->assertInertia(fn (Assert $page) => $page
            ->component('News/Show', false)->where('post.id', $archived->id)
            ->where('post.is_public', true)->where('post.status', 'archived'));
    }
});

test('archived posts can be filtered in the administration', function () {
    $archived = Post::factory()->create(['status' => 'archived']);
    Post::factory()->create();
    Post::factory()->draft()->create();
    $editor = User::factory()->create(['is_admin' => true, 'role' => 'editor']);

    $this->actingAs($editor)->get('/admin/posts?status=archived')->assertInertia(fn (Assert $page) => $page
        ->component('Admin/Posts/Index', false)->has('posts.data', 1)
        ->where('posts.data.0.id', $archived->id)->where('posts.data.0.status', 'archived'));
});

test('archived posts remain searchable and counted in their public category', function () {
    $category = PostCategory::factory()->create();
    $post = Post::factory()->create([
        'status' => 'archived', 'post_category_id' => $category->id,
        'title' => ['fr' => 'Colloque historique'],
    ]);

    $this->get('/actualites?categorie='.$category->slug.'&q=historique')->assertInertia(fn (Assert $page) => $page
        ->has('posts.data', 1)->where('posts.data.0.id', $post->id)->where('posts.data.0.status', 'archived')
        ->has('categories', 1)->where('categories.0.count', 1));
});

test('archived posts with a future publication date remain hidden until that date', function () {
    $post = Post::factory()->create(['status' => 'archived', 'published_at' => now()->addDay()]);

    expect($post->isPublic())->toBeFalse();
    $this->get(route('news.show', $post->slug))->assertNotFound();
    $this->get('/actualites')->assertInertia(fn (Assert $page) => $page->where('featured', null)->has('posts.data', 0));
});

test('archived posts can be republished without losing their original publication date', function () {
    $post = Post::factory()->create(['status' => 'archived', 'published_at' => now()->subDay()->startOfMinute()]);
    $publishedAt = $post->published_at;
    $editor = User::factory()->create(['is_admin' => true, 'role' => 'editor']);

    $this->actingAs($editor)->put("/admin/posts/{$post->id}", [...$post->toForm(), 'status' => 'published'])
        ->assertRedirect(route('admin.posts.edit', $post))->assertSessionHasNoErrors();

    expect($post->refresh()->status)->toBe('published')->and($post->isPublic())->toBeTrue()
        ->and($post->published_at->equalTo($publishedAt))->toBeTrue();
});

test('bulk archiving affects only selected posts and stays within the posts module', function () {
    $posts = Post::factory()->count(2)->create();
    $untouched = Post::factory()->create();
    $editor = User::factory()->create(['is_admin' => true, 'role' => 'editor']);

    $this->actingAs($editor)->withSession(['_previous' => ['url' => route('admin.users.index')]])
        ->post('/admin/posts/bulk', ['action' => 'archive', 'ids' => $posts->pluck('id')->all()])
        ->assertRedirect(route('admin.posts.index'))->assertSessionHas('success', '2 actualité(s) archivée(s).');

    expect(Post::whereKey($posts->pluck('id'))->where('status', 'archived')->count())->toBe(2)
        ->and($untouched->fresh()->status)->toBe('published');
    $this->assertDatabaseCount('posts', 3);
});

test('invalid archived content stays on its own form with validation errors', function () {
    $post = Post::factory()->create();
    $editor = User::factory()->create(['is_admin' => true, 'role' => 'editor']);

    $this->actingAs($editor)->withSession(['_previous' => ['url' => route('admin.users.index')]])
        ->put("/admin/posts/{$post->id}", ['title' => ['fr' => ''], 'status' => 'archived'])
        ->assertRedirect(route('admin.posts.edit', $post))->assertSessionHasErrors('title.fr');
    expect($post->fresh()->status)->toBe('published');
});
