<?php

use App\Mail\NewsletterItemMail;
use App\Mail\NewsletterVerificationMail;
use App\Models\Event;
use App\Models\Journal;
use App\Models\JournalIssue;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscription;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;

function newsletterEditor(): User
{
    return User::factory()->create(['is_admin' => true, 'role' => 'editor']);
}

function postPayload(array $overrides = []): array
{
    return array_merge([
        'title' => ['fr' => 'Soutenance de thèse'],
        'body' => ['fr' => '<p>Corps</p>'],
        'status' => 'published',
        'notify_subscribers' => true,
    ], $overrides);
}

test('visitors request a subscription without activating it and duplicates do not resend mail', function () {
    Mail::fake();
    $this->post('/newsletter', ['email' => 'Doctorant@Example.MG'])->assertRedirect(route('newsletter.confirmation'));
    $subscription = NewsletterSubscription::query()->sole();
    expect($subscription->email)->toBe('doctorant@example.mg')->and($subscription->token)->not->toBeEmpty()
        ->and($subscription->status)->toBe('pending')->and($subscription->subscribed_at)->toBeNull();

    $this->post('/newsletter', ['email' => 'doctorant@example.mg']);
    expect(NewsletterSubscription::query()->count())->toBe(1)->and($subscription->fresh()->status)->toBe('pending');
    Mail::assertSent(NewsletterVerificationMail::class, 1);
    $this->get('/newsletter/confirmation')->assertInertia(fn (Assert $page) => $page->where('status', 'pending')->where('email', 'doctorant@example.mg'));
});

test('the unsubscribe link shows a confirmation and works in one click without csrf', function () {
    $subscription = NewsletterSubscription::factory()->active()->create(['email' => 'lecteur@example.mg']);

    $this->get("/newsletter/desabonnement/{$subscription->token}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('NewsletterUnsubscribe', false)->where('unsubscribed', false));

    $this->post("/newsletter/desabonnement/{$subscription->token}", ['List-Unsubscribe' => 'One-Click'])->assertOk();
    expect($subscription->fresh()->status)->toBe('unsubscribed');

    $this->get('/newsletter/desabonnement/'.str_repeat('x', 48))->assertNotFound();
});

test('publishing a post with the box checked emails active subscribers once', function () {
    Mail::fake();
    NewsletterSubscription::factory()->active()->create(['email' => 'a@example.mg']);
    NewsletterSubscription::factory()->active()->create(['email' => 'b@example.mg', 'locale' => 'en']);
    NewsletterSubscription::factory()->active()->create(['email' => 'c@example.mg'])->unsubscribe();
    $editor = newsletterEditor();

    $this->actingAs($editor)->post('/admin/posts', postPayload())->assertSessionHas('success');

    Mail::assertSent(NewsletterItemMail::class, 2);
    Mail::assertSent(NewsletterItemMail::class, fn (NewsletterItemMail $mail) => $mail->hasTo('b@example.mg') && $mail->content['cta'] === 'Read the article');
    Mail::assertNotSent(NewsletterItemMail::class, fn (NewsletterItemMail $mail) => $mail->hasTo('c@example.mg'));

    $campaign = NewsletterCampaign::query()->sole();
    expect($campaign->status)->toBe('sent')->and($campaign->sent_count)->toBe(2);

    // Réenregistrer avec la case cochée ne renvoie pas
    $post = Post::query()->sole();
    $this->actingAs($editor)->put("/admin/posts/{$post->id}", postPayload(['title' => ['fr' => 'Titre modifié']]));
    Mail::assertSent(NewsletterItemMail::class, 2);
    expect(NewsletterCampaign::query()->count())->toBe(1);
});

test('a draft or future post waits for its publication before being sent', function () {
    Mail::fake();
    NewsletterSubscription::factory()->active()->create(['email' => 'a@example.mg']);
    $editor = newsletterEditor();

    $this->actingAs($editor)->post('/admin/posts', postPayload(['status' => 'draft']));
    Mail::assertNothingSent();
    expect(NewsletterCampaign::query()->sole()->status)->toBe('scheduled');

    $post = Post::query()->sole();
    $future = now()->addDay()->timezone(config('eden.timezone'))->format('Y-m-d\TH:i');
    $this->actingAs($editor)->put("/admin/posts/{$post->id}", postPayload(['notify_subscribers' => false, 'published_at' => $future]));

    $this->artisan('newsletter:dispatch')->assertSuccessful();
    Mail::assertNothingSent();

    $this->travel(25)->hours();
    $this->artisan('newsletter:dispatch')->assertSuccessful();
    Mail::assertSent(NewsletterItemMail::class, 1);
    expect(NewsletterCampaign::query()->sole()->status)->toBe('sent');
});

test('events and journal issues can be sent to subscribers', function () {
    Mail::fake();
    NewsletterSubscription::factory()->active()->create(['email' => 'a@example.mg']);
    $editor = newsletterEditor();
    $event = Event::factory()->create();
    $issue = JournalIssue::factory()->for(Journal::factory())->create();

    $this->actingAs($editor)->put("/admin/events/{$event->id}", [...$event->toForm(), 'is_all_day' => (bool) $event->is_all_day, 'is_featured' => (bool) $event->is_featured, 'notify_subscribers' => true])->assertSessionHasNoErrors();
    $this->actingAs($editor)->put("/admin/journals/{$issue->journal_id}/issues/{$issue->id}", [...$issue->toForm(), 'notify_subscribers' => true])->assertSessionHasNoErrors();

    Mail::assertSent(NewsletterItemMail::class, 2);
});

test('a scheduled campaign can be cancelled from the admin', function () {
    NewsletterSubscription::factory()->active()->create(['email' => 'a@example.mg']);
    $editor = newsletterEditor();
    $this->actingAs($editor)->post('/admin/posts', postPayload(['status' => 'draft']));
    $campaign = NewsletterCampaign::query()->sole();

    $this->actingAs($editor)->post("/admin/newsletter/campaigns/{$campaign->id}/cancel")->assertRedirect();
    expect($campaign->fresh()->status)->toBe('cancelled');
    $this->actingAs($editor)->get('/admin/newsletter/campaigns')->assertOk()->assertInertia(fn (Assert $page) => $page->has('campaigns.data', 1));
});

test('admins add subscribers manually or in bulk and export them', function () {
    Mail::fake();
    $editor = newsletterEditor();

    $this->actingAs($editor)->post('/admin/newsletter/subscribers', ['emails' => 'un@example.mg', 'name' => 'Un', 'locale' => 'fr'])->assertSessionHasNoErrors();
    $this->actingAs($editor)->post('/admin/newsletter/subscribers', ['emails' => "deux@example.mg\ntrois@example.mg, un@example.mg", 'locale' => 'en'])->assertSessionHasNoErrors();
    $this->actingAs($editor)->post('/admin/newsletter/subscribers', ['emails' => 'pas-un-email', 'locale' => 'fr'])->assertSessionHasErrors('emails');

    expect(NewsletterSubscription::query()->count())->toBe(3)
        ->and(NewsletterSubscription::query()->where('email', 'un@example.mg')->value('source'))->toBe('manual')
        ->and(NewsletterSubscription::query()->where('email', 'deux@example.mg')->value('source'))->toBe('import');

    $this->actingAs($editor)->get('/admin/newsletter')->assertOk()->assertInertia(fn (Assert $page) => $page->where('counts.active', 0)->where('counts.pending', 3));
    Mail::assertSent(NewsletterVerificationMail::class, 3);
    $csv = $this->actingAs($editor)->get('/admin/newsletter/export')->assertOk()->streamedContent();
    expect($csv)->toContain('deux@example.mg')->toContain('email;nom;statut');
});

test('the newsletter email contains the unsubscribe link and header', function () {
    $subscription = NewsletterSubscription::factory()->active()->create(['email' => 'a@example.mg']);
    $post = Post::factory()->create(['title' => ['fr' => 'Journée scientifique']]);
    $mail = new NewsletterItemMail($post, $subscription);

    $mail->assertSeeInHtml('Journée scientifique');
    $mail->assertSeeInHtml($subscription->unsubscribeUrl(), false);
    expect($mail->headers()->text['List-Unsubscribe'])->toContain($subscription->token);
});
