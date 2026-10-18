<?php

use App\Mail\UserInvitationMail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

test('inviting an account sends the styled email with its personal activation link', function (string $role, string $label) {
    Mail::fake();
    $admin = User::factory()->create(['is_admin' => true, 'role' => 'superadmin']);

    $this->actingAs($admin)->from('/admin/users')->post('/admin/users', [
        'name' => 'Nouvelle personne', 'email' => 'invited@example.com', 'role' => $role,
    ])->assertRedirect('/admin/users')->assertSessionHasNoErrors();

    $user = User::where('email', 'invited@example.com')->firstOrFail();
    Mail::assertSent(UserInvitationMail::class, function (UserInvitationMail $mail) use ($user, $label) {
        $token = basename(parse_url($mail->invitationUrl, PHP_URL_PATH));
        expect($mail->recipientName)->toBe($user->name)
            ->and($mail->roleLabel)->toBe($label)
            ->and(strlen($token))->toBe(64)
            ->and(hash('sha256', $token))->toBe($user->invitation_token)
            ->and($mail->invitationUrl)->toBe(route('invitation.accept.form', ['token' => $token]));
        $mail->assertSeeInHtml('Créer mon mot de passe');
        $mail->assertSeeInHtml('href="'.$mail->invitationUrl.'"', false);
        $mail->assertSeeInHtml('48 heures');
        $mail->assertSeeInText($mail->invitationUrl);
        $mail->assertSeeInText('48 heures');

        return $mail->hasTo($user->email);
    });
    Mail::assertSentCount(1);
    expect($user->email_verified_at)->toBeNull();
})->with([['editor', 'Éditeur'], ['admin', 'Admin']]);

test('invitation template escapes recipient names', function () {
    $mail = new UserInvitationMail('<script>alert(1)</script>', 'Éditeur', 'https://example.com/invitation/'.Str::random(64));

    $mail->assertSeeInHtml('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    $mail->assertDontSeeInHtml('<script>', false);
});

test('creating a password through the invitation confirms the email immediately', function () {
    $token = Str::random(64);
    $user = User::factory()->unverified()->create([
        'is_admin' => true, 'role' => 'editor',
        'invitation_token' => hash('sha256', $token), 'invitation_expires_at' => now()->addHours(48),
    ]);

    $this->get('/invitation/'.$token)->assertInertia(fn (Assert $page) => $page
        ->component('Auth/AcceptInvitation', false)->where('email', $user->email)->where('valid', true));
    expect($user->fresh()->email_verified_at)->toBeNull();

    $this->post('/invitation/'.$token, [
        'password' => 'Invitation-test-password', 'password_confirmation' => 'Invitation-test-password',
    ])->assertRedirect(route('login'))->assertSessionHasNoErrors()->assertSessionHas('success');

    expect($user->refresh()->email_verified_at)->not->toBeNull()
        ->and($user->invitation_token)->toBeNull()
        ->and($user->invitation_expires_at)->toBeNull()
        ->and(Hash::check('Invitation-test-password', $user->password))->toBeTrue();
    $this->assertGuest();

    $this->post('/invitation/'.$token, [
        'password' => 'Another-test-password', 'password_confirmation' => 'Another-test-password',
    ])->assertSessionHasErrors('token');
    expect(Hash::check('Invitation-test-password', $user->fresh()->password))->toBeTrue();
});
