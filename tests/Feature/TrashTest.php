<?php

use App\Models\ContentItem;
use App\Models\ContentRevision;
use App\Models\HeroSlide;
use App\Models\Journal;
use App\Models\JournalIssue;
use App\Models\NewsletterSubscription;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use App\Services\TrashRegistry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

test('each module can be moved to trash, listed, restored and permanently deleted', function (string $type, string $class) {
    $admin = User::factory()->create(['is_admin' => true, 'role' => 'superadmin']);
    $item = match ($class) {
        ContentItem::class => ContentItem::create(['collection_key' => 'mission_cards', 'locale' => 'fr', 'title' => 'Carte de test']),
        HeroSlide::class => HeroSlide::create(['image' => '/storage/content/test.jpg', 'title' => ['fr' => 'Diapositive'], 'link_type' => 'none']),
        default => $class::factory()->create(),
    };
    $item->delete();
    $this->assertSoftDeleted($item);
    expect($class::find($item->id))->toBeNull();

    $this->actingAs($admin)->get('/admin/trash?type='.$type)->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Admin/Trash/Index', false)->where('items.meta.total', 1)
        ->where('items.data.0.id', $item->id)->where('items.data.0.type', $type));
    $this->post("/admin/trash/{$type}/{$item->id}/restore")->assertRedirect('/admin/trash')->assertSessionHas('success');
    expect($class::find($item->id))->not->toBeNull();

    $class::findOrFail($item->id)->delete();
    $this->post("/admin/trash/{$type}/{$item->id}", ['_method' => 'DELETE'])->assertRedirect('/admin/trash');
    $this->assertDatabaseMissing($item->getTable(), ['id' => $item->id]);
})->with(collect(TrashRegistry::TYPES)->map(fn ($definition, $key) => [$key, $definition[0]])->values()->all());

test('deleting and restoring a journal preserves its issues and independently deleted issues', function () {
    $journal = Journal::factory()->create();
    $issue = JournalIssue::factory()->for($journal)->create(['pdf_url' => '/storage/documents/retained.pdf']);
    $previouslyDeleted = JournalIssue::factory()->for($journal)->create(['number' => 2, 'slug' => 'n02']);
    $previouslyDeleted->delete();
    $editor = User::factory()->create(['is_admin' => true, 'role' => 'editor']);

    $this->actingAs($editor)->delete("/admin/journals/{$journal->id}")->assertRedirect();
    $this->assertSoftDeleted($journal);
    $this->assertSoftDeleted($issue);
    $this->get('/admin/trash?type=journal-issues')->assertInertia(fn (Assert $page) => $page
        ->where('items.data.0.can_restore', false));
    $this->post("/admin/trash/journal-issues/{$issue->id}/restore")->assertSessionHas('error');
    $this->post("/admin/trash/journals/{$journal->id}/restore")->assertRedirect('/admin/trash');
    expect(JournalIssue::findOrFail($issue->id)->pdf_url)->toBe('/storage/documents/retained.pdf');
    $this->assertSoftDeleted($previouslyDeleted);

    $journal->refresh()->delete();
    $this->post("/admin/trash/journals/{$journal->id}", ['_method' => 'DELETE'])->assertRedirect('/admin/trash');
    $this->assertDatabaseMissing('journal_issues', ['journal_id' => $journal->id]);
});

test('category restoration restores its existing article associations', function () {
    $category = PostCategory::factory()->create();
    $post = Post::factory()->for($category, 'category')->create();
    $category->delete();
    expect($post->fresh()->category)->toBeNull();
    $editor = User::factory()->create(['is_admin' => true, 'role' => 'editor']);

    $this->actingAs($editor)->post("/admin/trash/post-categories/{$category->id}/restore")->assertRedirect();
    expect($post->fresh()->category->id)->toBe($category->id);
    $category->refresh()->delete();
    $this->post("/admin/trash/post-categories/{$category->id}", ['_method' => 'DELETE'])->assertRedirect();
    expect($post->fresh()->post_category_id)->toBeNull();
});

test('editors cannot see, restore or permanently delete user accounts from trash', function () {
    $account = User::factory()->create();
    $account->delete();
    $editor = User::factory()->create(['is_admin' => true, 'role' => 'editor']);
    $this->actingAs($editor)->get('/admin/trash')->assertInertia(fn (Assert $page) => $page
        ->where('items.meta.total', 0)->where('types', fn ($types) => collect($types)->where('value', 'users')->isEmpty()));
    $this->get('/admin/trash?type=users')->assertNotFound();
    $this->post("/admin/trash/users/{$account->id}/restore")->assertNotFound();
    $this->post("/admin/trash/users/{$account->id}", ['_method' => 'DELETE'])->assertNotFound();
    $this->assertSoftDeleted($account);
});

test('trash operations reject guests, unknown modules and active records', function () {
    $post = Post::factory()->create();
    $this->get('/admin/trash')->assertNotFound();
    $this->post("/admin/trash/posts/{$post->id}", ['_method' => 'DELETE'])->assertNotFound();
    $editor = User::factory()->create(['is_admin' => true, 'role' => 'editor']);
    $this->actingAs($editor)->post("/admin/trash/posts/{$post->id}", ['_method' => 'DELETE'])->assertNotFound();
    $this->post("/admin/trash/posts/{$post->id}/restore")->assertNotFound();
    $this->post('/admin/trash/unknown/1/restore')->assertNotFound();
    expect($post->fresh())->not->toBeNull();
});

test('trash pagination and search combine different modules without exposing model secrets', function () {
    Post::factory()->count(22)->create(['title' => ['fr' => 'Document à récupérer']])->each->delete();
    $account = User::factory()->create(['name' => 'Compte confidentiel']);
    $account->delete();
    $admin = User::factory()->create(['is_admin' => true, 'role' => 'superadmin']);
    $this->actingAs($admin)->get('/admin/trash')->assertInertia(fn (Assert $page) => $page
        ->where('items.meta.total', 23)->where('items.meta.last_page', 2)->has('items.data', 20));
    $this->get('/admin/trash?q=confidentiel')->assertInertia(fn (Assert $page) => $page
        ->where('items.meta.total', 1)->where('items.data.0.id', $account->id)
        ->missing('items.data.0.password')->missing('items.data.0.remember_token'));
});

test('trashed posts are no longer public and their slug stays reserved for restoration', function () {
    $post = Post::factory()->create(['title' => ['fr' => 'Titre réservé'], 'slug' => 'titre-reserve']);
    $post->delete();
    $this->get('/actualites/titre-reserve')->assertNotFound();
    $editor = User::factory()->create(['is_admin' => true, 'role' => 'editor']);
    $this->actingAs($editor)->post('/admin/posts', ['title' => ['fr' => 'Titre réservé'], 'status' => 'draft'])->assertSessionHasNoErrors();
    expect(Post::latest('id')->first()->slug)->toBe('titre-reserve-2');
    $this->post("/admin/trash/posts/{$post->id}/restore")->assertRedirect();
    expect(Post::findOrFail($post->id)->slug)->toBe('titre-reserve');
});

test('deleting an account revokes sessions and prevents login until restored', function () {
    config(['session.driver' => 'database']);
    $user = User::factory()->create(['is_admin' => true, 'role' => 'editor', 'password' => 'Test-account-password', 'remember_token' => 'old-token']);
    DB::table('sessions')->insert(['id' => 'test-session', 'user_id' => $user->id, 'payload' => '', 'last_activity' => now()->timestamp]);
    $user->delete();
    $this->assertDatabaseMissing('sessions', ['user_id' => $user->id]);
    expect($user->remember_token)->toBeNull()
        ->and(Auth::validate(['email' => $user->email, 'password' => 'Test-account-password', 'is_admin' => true]))->toBeFalse();
});

test('a trashed newsletter subscription is not recreated or silently resubscribed', function () {
    $subscription = NewsletterSubscription::factory()->create();
    $subscription->delete();
    $known = NewsletterSubscription::subscribe($subscription->email);
    expect($known->id)->toBe($subscription->id)->and($known->trashed())->toBeTrue()->and($known->isActive())->toBeFalse();
    $this->post('/newsletter', ['email' => $subscription->email])->assertSessionHasErrors('email');
    $this->assertSoftDeleted($subscription);
});

test('permanent deletion of page content also removes versions that could recreate it', function () {
    $item = ContentItem::create(['collection_key' => 'mission_cards', 'locale' => 'fr', 'title' => 'Carte']);
    $revision = ContentRevision::create(['content_type' => 'item', 'content_id' => $item->id, 'content_key' => 'mission_cards', 'action' => 'deleted', 'before' => $item->toArray()]);
    $item->delete();
    $editor = User::factory()->create(['is_admin' => true, 'role' => 'editor']);
    $this->actingAs($editor)->post("/admin/trash/content-items/{$item->id}", ['_method' => 'DELETE'])->assertRedirect();
    $this->assertModelMissing($item);
    $this->assertModelMissing($revision);
});
