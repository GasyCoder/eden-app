<?php

use App\Models\Announcement;
use App\Models\Event;
use App\Models\Journal;
use App\Models\JournalIssue;
use App\Models\NewsletterSubscription;
use App\Models\Post;
use App\Models\Project;
use App\Models\User;

function bulkEditor(): User
{
    return User::factory()->create(['is_admin' => true, 'role' => 'editor']);
}

test('posts can be published, featured and deleted in bulk', function () {
    $editor = bulkEditor();
    $drafts = Post::factory()->draft()->count(2)->create(['published_at' => null]);
    $untouched = Post::factory()->draft()->create();

    $this->actingAs($editor)->post('/admin/posts/bulk', ['action' => 'publish', 'ids' => $drafts->pluck('id')->all()])
        ->assertRedirect()->assertSessionHas('success', '2 actualité(s) publiée(s).');

    expect(Post::query()->whereKey($drafts->pluck('id'))->published()->count())->toBe(2)
        ->and($untouched->fresh()->status)->toBe('draft');

    $this->actingAs($editor)->post('/admin/posts/bulk', ['action' => 'feature', 'ids' => [$drafts[0]->id]]);
    expect($drafts[0]->fresh()->is_featured)->toBeTrue();

    $this->actingAs($editor)->post('/admin/posts/bulk', ['action' => 'delete', 'ids' => $drafts->pluck('id')->all()]);
    expect(Post::query()->count())->toBe(1);
});

test('bulk actions are validated', function () {
    $editor = bulkEditor();

    $this->actingAs($editor)->post('/admin/posts/bulk', ['action' => 'explode', 'ids' => [1]])->assertSessionHasErrors('action');
    $this->actingAs($editor)->post('/admin/posts/bulk', ['action' => 'delete', 'ids' => []])->assertSessionHasErrors('ids');
});

test('events, announcements, journals and projects support their bulk actions', function () {
    $editor = bulkEditor();
    $events = Event::factory()->count(2)->create();
    $announcements = Announcement::factory()->count(2)->create();
    $journal = Journal::factory()->create();
    JournalIssue::factory()->for($journal)->create();
    $projects = Project::factory()->count(2)->create();

    $this->actingAs($editor)->post('/admin/events/bulk', ['action' => 'cancel', 'ids' => $events->pluck('id')->all()]);
    $this->actingAs($editor)->post('/admin/announcements/bulk', ['action' => 'archive', 'ids' => $announcements->pluck('id')->all()]);
    $this->actingAs($editor)->post('/admin/projects/bulk', ['action' => 'draft', 'ids' => $projects->pluck('id')->all()]);
    $this->actingAs($editor)->post('/admin/journals/bulk', ['action' => 'delete', 'ids' => [$journal->id]]);

    expect(Event::query()->where('status', 'cancelled')->count())->toBe(2)
        ->and(Announcement::query()->where('status', 'archived')->count())->toBe(2)
        ->and(Project::query()->whereKey($projects->pluck('id'))->where('status', 'draft')->count())->toBe(2)
        ->and(Journal::query()->count())->toBe(0)
        ->and(JournalIssue::query()->count())->toBe(0);
});

test('newsletter subscribers can be unsubscribed and exported in bulk', function () {
    $editor = bulkEditor();
    $first = NewsletterSubscription::subscribe('un@example.mg');
    $second = NewsletterSubscription::subscribe('deux@example.mg');
    NewsletterSubscription::subscribe('trois@example.mg');

    $this->actingAs($editor)->post('/admin/newsletter/subscribers/bulk', ['action' => 'unsubscribe', 'ids' => [$first->id, $second->id]])
        ->assertSessionHas('success', '2 abonné(s) désinscrit(s).');
    expect(NewsletterSubscription::query()->active()->count())->toBe(1);

    $csv = $this->actingAs($editor)->get("/admin/newsletter/export?ids[]={$first->id}")->streamedContent();
    expect($csv)->toContain('un@example.mg')->not->toContain('trois@example.mg');
});

test('visitors without admin rights cannot run bulk actions', function () {
    $post = Post::factory()->create();

    $this->actingAs(User::factory()->create(['is_admin' => false]))
        ->post('/admin/posts/bulk', ['action' => 'delete', 'ids' => [$post->id]])
        ->assertForbidden();

    expect($post->fresh())->not->toBeNull();
});
