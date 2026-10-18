<?php

use App\Mail\ResetPasswordMail;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Inertia\Testing\AssertableInertia as Assert;

test('admin reset requests deliver the French EDEN email with HTML and text versions', function () {
    config(['mail.default' => 'array']);
    $admin = User::factory()->create(['is_admin' => true, 'role' => 'superadmin']);
    $user = User::factory()->create(['name' => 'Camille', 'is_admin' => true, 'role' => 'editor']);
    $originalPassword = $user->password;

    $this->actingAs($admin)->from('/admin/users')->post("/admin/users/{$user->id}/password-reset")
        ->assertRedirect('/admin/users')->assertSessionHas('success')->assertSessionHasNoErrors();

    $message = Mail::mailer()->getSymfonyTransport()->messages()->sole()->getOriginalMessage();
    expect($message->getSubject())->toBe('Réinitialisez votre mot de passe EDEN-UMG')
        ->and($message->getTo()[0]->getAddress())->toBe($user->email)
        ->and($message->getHtmlBody())->toContain('Bonjour Camille,', 'Réinitialiser mon mot de passe', 'EDEN · UMG', '60 minutes')
        ->not->toContain('laravel.com', 'Reset Password', 'Regards,')
        ->and($message->getTextBody())->toContain('RÉINITIALISER MON MOT DE PASSE', '60 minutes', rawurlencode($user->email))
        ->and($user->fresh()->password)->toBe($originalPassword);
});

test('reset email uses configured expiry, escapes names and preserves the email in its URL', function () {
    config(['auth.passwords.users.expire' => 25]);
    $user = User::factory()->make(['name' => '<b>Camille</b>', 'email' => 'camille+eden@example.com']);
    $mail = (new ResetPassword('test-token'))->toMail($user);

    expect($mail)->toBeInstanceOf(ResetPasswordMail::class)
        ->and($mail->expiresInMinutes)->toBe(25);
    $mail->assertSeeInHtml('25 minutes');
    $mail->assertSeeInText('25 minutes');
    $mail->assertSeeInHtml('&lt;b&gt;Camille&lt;/b&gt;', false);
    $mail->assertDontSeeInHtml('<b>Camille</b>', false);
    parse_str(parse_url($mail->resetUrl, PHP_URL_QUERY), $query);
    expect($query['email'])->toBe($user->email)
        ->and(parse_url($mail->resetUrl, PHP_URL_PATH))->toBe('/password/reset/test-token')
        ->and($mail->hasTo($user->email))->toBeTrue();
});

test('link from the styled email resets the password and cannot be reused', function () {
    config(['mail.default' => 'array']);
    $user = User::factory()->create(['is_admin' => true, 'role' => 'editor']);

    expect(Password::sendResetLink(['email' => $user->email]))->toBe(Password::RESET_LINK_SENT);
    $message = Mail::mailer()->getSymfonyTransport()->messages()->sole()->getOriginalMessage();
    preg_match('~https?://[^\s]+/password/reset/[^\s]+~u', $message->getTextBody(), $matches);
    $url = $matches[0];
    $token = basename(parse_url($url, PHP_URL_PATH));

    $this->get($url)->assertInertia(fn (Assert $page) => $page
        ->component('Auth/ResetPassword', false)->where('token', $token)->where('email', $user->email));
    $credentials = [
        'token' => $token, 'email' => $user->email,
        'password' => 'Updated-test-password', 'password_confirmation' => 'Updated-test-password',
    ];
    $this->post('/password/reset', $credentials)->assertRedirect(route('login'))->assertSessionHasNoErrors();
    expect(Hash::check($credentials['password'], $user->fresh()->password))->toBeTrue();
    $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);

    $this->from($url)->post('/password/reset', [
        ...$credentials, 'password' => 'Another-test-password', 'password_confirmation' => 'Another-test-password',
    ])->assertSessionHasErrors('email');
    expect(Hash::check($credentials['password'], $user->fresh()->password))->toBeTrue();
});
