<?php

use App\Jobs\SendNewsletterEmail;
use App\Mail\NewsletterItemMail;
use App\Mail\NewsletterVerificationMail;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterDelivery;
use App\Models\NewsletterSubscription;
use App\Models\Post;
use App\Models\User;
use App\Services\Newsletter\SubscriptionVerification;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;
use Symfony\Component\Mailer\Exception\TransportException;

function newsletterApprovalAdmin(): User
{
    return User::factory()->create(['is_admin' => true, 'role' => 'editor']);
}

function newsletterVerificationUrl(NewsletterSubscription $subscriber): string
{
    return URL::temporarySignedRoute('newsletter.verify', now()->addDay(), ['subscriber' => $subscriber->id, 'token' => $subscriber->fresh()->verification_token]);
}

test('confirmation of ownership waits for admin approval and the link is idempotent', function () {
    Mail::fake();
    $this->post('/newsletter', ['email' => 'lecteur@example.mg'])->assertSessionHasNoErrors();
    $subscriber = NewsletterSubscription::query()->sole();
    $url = Mail::sent(NewsletterVerificationMail::class)->sole()->verificationUrl;

    Mail::assertSent(NewsletterVerificationMail::class, fn ($mail) => $mail->hasTo($subscriber->email) && $mail->verificationUrl === $url);
    $this->get($url)->assertRedirect(route('newsletter.confirmation'));
    $verifiedAt = $subscriber->fresh()->email_verified_at;
    expect($verifiedAt)->not->toBeNull()->and($subscriber->fresh()->status)->toBe('pending_approval')
        ->and(NewsletterSubscription::query()->active()->count())->toBe(0);
    $this->get('/newsletter/confirmation')->assertInertia(fn (Assert $page) => $page->where('status', 'pending_approval'));

    $this->actingAs(newsletterApprovalAdmin())->patch("/admin/newsletter/subscribers/{$subscriber->id}", ['status' => 'subscribed'])->assertSessionHasNoErrors();
    expect($subscriber->fresh()->isActive())->toBeTrue()->and($subscriber->fresh()->approved_at)->not->toBeNull();
    $this->get($url)->assertRedirect();
    expect($subscriber->fresh()->status)->toBe('subscribed')->and($subscriber->fresh()->email_verified_at->equalTo($verifiedAt))->toBeTrue();
});

test('an administrator can directly confirm a pending address but cannot reactivate an unsubscribed one', function () {
    $pending = NewsletterSubscription::factory()->create();
    $admin = newsletterApprovalAdmin();
    $this->actingAs($admin)->patch("/admin/newsletter/subscribers/{$pending->id}", ['status' => 'subscribed'])->assertSessionHasNoErrors();
    expect($pending->fresh()->isActive())->toBeTrue()->and($pending->fresh()->email_verified_at)->toBeNull()
        ->and($pending->fresh()->admin_confirmed_at)->not->toBeNull()->and($pending->fresh()->approved_by)->toBe($admin->id);

    $pending->unsubscribe();
    $this->actingAs($admin)->patch("/admin/newsletter/subscribers/{$pending->id}", ['status' => 'subscribed'])->assertSessionHasErrors('status');
    expect($pending->fresh()->isActive())->toBeFalse();
});

test('tampered expired and unsubscribe tokens cannot verify an email', function () {
    Mail::fake();
    $subscriber = NewsletterSubscription::subscribe('lecteur@example.mg');
    app(SubscriptionVerification::class)->send($subscriber);
    $url = newsletterVerificationUrl($subscriber);

    $this->get($url.'&extra=changed')->assertForbidden();
    $this->get(URL::temporarySignedRoute('newsletter.verify', now()->addDay(), ['subscriber' => $subscriber->id, 'token' => str_repeat('x', 64)]))->assertNotFound();
    $this->get(URL::temporarySignedRoute('newsletter.verify', now()->addDay(), ['subscriber' => $subscriber->id, 'token' => str_pad($subscriber->token, 64, 'x')]))->assertNotFound();
    $this->travel(25)->hours();
    $this->get($url)->assertForbidden();
    expect($subscriber->fresh()->email_verified_at)->toBeNull()->and($subscriber->fresh()->isActive())->toBeFalse();
});

test('resending confirmation is session scoped rate limited and invalidates old links', function () {
    Mail::fake();
    $other = NewsletterSubscription::factory()->create();
    $this->post('/newsletter', ['email' => 'lecteur@example.mg']);
    $subscriber = NewsletterSubscription::query()->where('email', 'lecteur@example.mg')->sole();
    $old = newsletterVerificationUrl($subscriber);

    $this->post('/newsletter/confirmation/renvoyer')->assertSessionHasErrors('email');
    Mail::assertSent(NewsletterVerificationMail::class, 1);
    $this->travel(61)->seconds();
    // Même si un id arbitraire est envoyé, seule la demande de la session est utilisée.
    $this->post('/newsletter/confirmation/renvoyer', ['subscriber' => $other->id])->assertSessionHasNoErrors()->assertSessionHas('newsletter_resent', true);
    Mail::assertSent(NewsletterVerificationMail::class, 2);
    expect($other->fresh()->verification_sent_at)->toBeNull();
    $this->get($old)->assertNotFound();
    $this->get(newsletterVerificationUrl($subscriber))->assertRedirect();
    expect($subscriber->fresh()->status)->toBe('pending_approval');
});

test('a missing subscription session cannot resend or show someone elses email', function () {
    NewsletterSubscription::factory()->create();
    $this->post('/newsletter/confirmation/renvoyer')->assertNotFound();
    $this->get('/newsletter/confirmation')->assertRedirect(route('home'));
});

test('an administrator may refuse a request and its old verification link no longer works', function () {
    Mail::fake();
    $this->post('/newsletter', ['email' => 'refuse@example.mg']);
    $subscriber = NewsletterSubscription::query()->sole();
    $url = newsletterVerificationUrl($subscriber);

    $this->actingAs(newsletterApprovalAdmin())->patch("/admin/newsletter/subscribers/{$subscriber->id}", ['status' => 'rejected'])->assertSessionHasNoErrors();
    expect($subscriber->fresh()->status)->toBe('rejected')->and($subscriber->fresh()->rejected_at)->not->toBeNull();
    $this->get($url)->assertNotFound();
    $this->post('/newsletter', ['email' => 'REFUSE@example.mg'])->assertSessionHasErrors('email');
    expect(NewsletterSubscription::query()->count())->toBe(1)->and($subscriber->fresh()->status)->toBe('rejected');
});

test('active and unsubscribed addresses cannot be silently reactivated by public or admin imports', function () {
    Mail::fake();
    $subscriber = NewsletterSubscription::factory()->active()->create(['email' => 'lecteur@example.mg', 'locale' => 'fr']);
    $this->post('/newsletter', ['email' => 'LECTEUR@example.mg'])->assertSessionHasErrors('email');
    $subscriber->unsubscribe();
    $this->post('/newsletter', ['email' => 'lecteur@example.mg'])->assertSessionHasErrors('email');
    $this->actingAs(newsletterApprovalAdmin())->post('/admin/newsletter/subscribers', ['emails' => 'lecteur@example.mg', 'locale' => 'en'])->assertSessionHasNoErrors();
    expect($subscriber->fresh()->status)->toBe('unsubscribed')->and($subscriber->fresh()->locale)->toBe('fr');
    Mail::assertNothingSent();
});

test('email normalization and the database unique index prevent duplicates', function () {
    $first = NewsletterSubscription::subscribe('Lecteur@Example.MG');
    $second = NewsletterSubscription::subscribe(' lecteur@example.mg ');
    expect($first->id)->toBe($second->id)->and(NewsletterSubscription::query()->count())->toBe(1);

    expect(fn () => NewsletterSubscription::create(['email' => ' LECTEUR@example.mg ']))->toThrow(UniqueConstraintViolationException::class);
});

test('bulk direct approval is recorded and cannot reactivate a refused address', function () {
    $verified = NewsletterSubscription::factory()->verified()->create();
    $pending = NewsletterSubscription::factory()->create();
    $refused = NewsletterSubscription::factory()->create(['status' => 'rejected']);
    $admin = newsletterApprovalAdmin();
    $this->actingAs($admin)->post('/admin/newsletter/subscribers/bulk', ['action' => 'approve', 'ids' => [$verified->id, $pending->id, $refused->id]])->assertSessionHasErrors('status');
    expect($verified->fresh()->status)->toBe('pending_approval')->and($pending->fresh()->status)->toBe('pending');
    $this->actingAs($admin)->post('/admin/newsletter/subscribers/bulk', ['action' => 'approve', 'ids' => [$verified->id, $pending->id]])->assertSessionHasNoErrors();
    expect($verified->fresh()->isActive())->toBeTrue()->and($pending->fresh()->isActive())->toBeTrue()
        ->and($pending->fresh()->email_verified_at)->toBeNull()->and($pending->fresh()->approved_by)->toBe($admin->id);
    $this->actingAs($admin)->post('/admin/newsletter/subscribers/bulk', ['action' => 'resubscribe', 'ids' => [$pending->id]])->assertSessionHasErrors('action');
    $this->actingAs($admin)->post('/admin/newsletter/subscribers/bulk', ['action' => 'reject', 'ids' => [$verified->id, $pending->id]])->assertSessionHasNoErrors();
    expect(NewsletterSubscription::query()->where('status', 'rejected')->count())->toBe(3);
});

test('newsletter moderation and confirmation resend require administrator privileges', function () {
    Mail::fake();
    $subscriber = NewsletterSubscription::factory()->verified()->create();
    $this->patch("/admin/newsletter/subscribers/{$subscriber->id}", ['status' => 'subscribed'])->assertNotFound();
    $this->actingAs(User::factory()->create(['is_admin' => false]))
        ->patch("/admin/newsletter/subscribers/{$subscriber->id}", ['status' => 'subscribed'])->assertNotFound();
    $this->post("/admin/newsletter/subscribers/{$subscriber->id}/verification")->assertNotFound();
    $this->post('/admin/newsletter/subscribers/bulk', ['action' => 'approve', 'ids' => [$subscriber->id]])->assertNotFound();
    expect($subscriber->fresh()->isActive())->toBeFalse();
    Mail::assertNothingSent();
});

test('queued deliveries check verification and approval again before sending', function () {
    Mail::fake();
    $post = Post::factory()->create();
    $campaign = NewsletterCampaign::create(['subject' => 'Test', 'notifiable_type' => $post->getMorphClass(), 'notifiable_id' => $post->id]);
    $pending = NewsletterSubscription::factory()->create();
    $verified = NewsletterSubscription::factory()->verified()->create();
    $incomplete = NewsletterSubscription::factory()->create(['status' => 'subscribed']);
    $rejected = NewsletterSubscription::factory()->active()->create();
    $rejected->reject();
    $unsubscribed = NewsletterSubscription::factory()->active()->create();
    $unsubscribed->unsubscribe();
    $active = NewsletterSubscription::factory()->active()->create();
    $direct = NewsletterSubscription::factory()->create();
    $direct->approve(newsletterApprovalAdmin());

    foreach ([$pending, $verified, $incomplete, $rejected, $unsubscribed, $active, $direct] as $subscriber) {
        $delivery = NewsletterDelivery::create(['newsletter_campaign_id' => $campaign->id, 'newsletter_subscription_id' => $subscriber->id]);
        (new SendNewsletterEmail($delivery->id))->handle();
        expect($delivery->fresh()->status)->toBe($subscriber->isActive() ? 'sent' : 'skipped');
    }

    Mail::assertSent(NewsletterItemMail::class, 2);
    Mail::assertSent(NewsletterItemMail::class, fn ($mail) => $mail->hasTo($active->email));
    Mail::assertSent(NewsletterItemMail::class, fn ($mail) => $mail->hasTo($direct->email));
    expect(NewsletterSubscription::query()->active()->count())->toBe(2);
});

test('mail failure keeps the request inactive and lets the visitor retry', function () {
    Mail::shouldReceive('to')->once()->andThrow(new TransportException('Test transport failure'));
    $this->post('/newsletter', ['email' => 'lecteur@example.mg'])->assertRedirect(route('newsletter.confirmation'))->assertSessionHas('newsletter_mail_error', true);
    $subscriber = NewsletterSubscription::query()->sole();
    expect($subscriber->status)->toBe('pending')->and($subscriber->verification_sent_at)->toBeNull()->and($subscriber->verification_token)->toBeNull();
    $this->get('/newsletter/confirmation')->assertInertia(fn (Assert $page) => $page->where('mailError', true)->where('status', 'pending'));
});

test('admin can resend verification and filter the moderation queue without exposing tokens', function () {
    Mail::fake();
    $pending = NewsletterSubscription::factory()->create();
    NewsletterSubscription::factory()->verified()->create();
    $this->actingAs(newsletterApprovalAdmin())->post("/admin/newsletter/subscribers/{$pending->id}/verification")->assertSessionHasNoErrors();
    Mail::assertSent(NewsletterVerificationMail::class, 1);
    $this->get('/admin/newsletter?status=pending_approval')->assertInertia(fn (Assert $page) => $page
        ->where('counts.pending', 1)->where('counts.pending_approval', 1)->where('counts.active', 0)
        ->has('subscribers.data', 1)->where('subscribers.data.0.status', 'pending_approval')
        ->missing('subscribers.data.0.token')->missing('subscribers.data.0.verification_token'));
});

test('legacy subscriptions are retained but must confirm and be approved', function () {
    $migration = require database_path('migrations/2026_10_18_000005_secure_newsletter_subscriptions.php');
    $migration->down();
    $id = DB::table('newsletter_subscriptions')->insertGetId(['email' => 'historique@example.mg', 'status' => 'subscribed', 'token' => str_repeat('z', 48), 'created_at' => now(), 'updated_at' => now()]);
    $migration->up();
    $subscriber = NewsletterSubscription::findOrFail($id);
    expect($subscriber->status)->toBe('pending')->and($subscriber->email_verified_at)->toBeNull()->and($subscriber->approved_at)->toBeNull()->and($subscriber->isActive())->toBeFalse();
});

test('an existing pending address with no successful delivery receives its first confirmation', function () {
    Mail::fake();
    $subscriber = NewsletterSubscription::factory()->create(['email' => 'historique@example.mg']);
    $this->post('/newsletter', ['email' => 'HISTORIQUE@example.mg'])->assertSessionHasNoErrors()->assertRedirect(route('newsletter.confirmation'));
    Mail::assertSent(NewsletterVerificationMail::class, 1);
    expect(NewsletterSubscription::query()->count())->toBe(1)->and($subscriber->fresh()->verification_sent_at)->not->toBeNull()
        ->and($subscriber->fresh()->isActive())->toBeFalse();
});

test('confirmation messages explain the two steps in French and English', function () {
    $url = 'https://eden.example/newsletter/verifier/1/'.str_repeat('x', 64).'?expires=123&signature=test';
    $french = new NewsletterVerificationMail($url, 'fr');
    $french->assertSeeInHtml('Confirmer mon e-mail');
    $french->assertSeeInHtml('24 heures');
    $french->assertSeeInHtml($url);
    $english = new NewsletterVerificationMail($url, 'en');
    $english->assertSeeInHtml('Confirm my email');
    $english->assertSeeInHtml('administrator');
    $english->assertSeeInHtml('24 hours');
});

test('public signup is rate limited and delimiter only imports are rejected', function () {
    Mail::fake();
    for ($i = 0; $i < 5; $i++) {
        $this->post('/newsletter', ['email' => "lecteur{$i}@example.mg"])->assertSessionHasNoErrors();
    }
    $this->post('/newsletter', ['email' => 'excess@example.mg'])->assertTooManyRequests();
    expect(NewsletterSubscription::query()->count())->toBe(5);
    $this->actingAs(newsletterApprovalAdmin())->post('/admin/newsletter/subscribers', ['emails' => ',,;', 'locale' => 'fr'])->assertSessionHasErrors('emails');
});
