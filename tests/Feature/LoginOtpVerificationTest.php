<?php

use App\Mail\LoginOtpMail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;

test('successful email OTP confirms the invited account and clears its invitation', function () {
    Mail::fake();
    $user = User::factory()->unverified()->create([
        'is_admin' => true, 'role' => 'superadmin', 'password' => 'Test-only-password-2026',
        'invitation_token' => hash('sha256', 'test-invitation'),
        'invitation_expires_at' => now()->addHours(48),
    ]);
    $password = $user->password;

    $this->from('/me/login')->post('/me/login', [
        'email' => $user->email, 'password' => 'Test-only-password-2026',
    ])->assertRedirect('/me/login')->assertSessionHasNoErrors();
    $this->assertGuest();
    expect($user->fresh()->email_verified_at)->toBeNull();

    $mail = Mail::sent(LoginOtpMail::class)->sole();
    expect($mail->hasTo($user->email))->toBeTrue();
    $this->post('/me/login/otp', ['code' => $mail->code])->assertRedirect()->assertSessionHasNoErrors();
    $this->assertAuthenticatedAs($user);

    expect($user->refresh()->email_verified_at)->not->toBeNull()
        ->and($user->invitation_token)->toBeNull()
        ->and($user->invitation_expires_at)->toBeNull()
        ->and($user->password)->toBe($password);

    $this->get('/admin/users')->assertInertia(fn (Assert $page) => $page
        ->component('Admin/Users/Index', false)
        ->where('users.data.0.email_verified', true)
        ->where('users.data.0.invitation_pending', false));
});

test('rejected OTP does not confirm an account or clear its invitation', function (string $failure) {
    $token = hash('sha256', 'pending-invitation');
    $user = User::factory()->unverified()->create([
        'is_admin' => true, 'role' => 'editor', 'invitation_token' => $token,
        'invitation_expires_at' => now()->addHours(48),
    ]);

    $this->withSession([
        'login_otp_user_id' => $user->id,
        'login_otp_email' => $failure === 'email changed' ? 'old@example.com' : $user->email,
        'login_otp_hash' => Hash::make('123456'),
        'login_otp_expires_at' => $failure === 'expired' ? now()->subMinute()->timestamp : now()->addMinutes(10)->timestamp,
        'login_otp_attempts' => 0,
    ])->from('/me/login')->post('/me/login/otp', [
        'code' => $failure === 'wrong code' ? '654321' : '123456',
    ])->assertRedirect('/me/login')->assertSessionHasErrors('code');

    $this->assertGuest();
    expect($user->refresh()->email_verified_at)->toBeNull()->and($user->invitation_token)->toBe($token);
})->with(['wrong code', 'expired', 'email changed']);

test('another successful OTP preserves the original email confirmation date', function () {
    $user = User::factory()->create([
        'is_admin' => true, 'role' => 'editor', 'email_verified_at' => now()->subDay(),
        'invitation_token' => hash('sha256', 'stale-invitation'),
        'invitation_expires_at' => now()->addDay(),
    ]);
    $verifiedAt = $user->email_verified_at;

    $this->withSession([
        'login_otp_user_id' => $user->id, 'login_otp_email' => $user->email,
        'login_otp_hash' => Hash::make('123456'), 'login_otp_expires_at' => now()->addMinutes(10)->timestamp,
        'login_otp_attempts' => 0,
    ])->post('/me/login/otp', ['code' => '123456'])->assertRedirect()->assertSessionHasNoErrors();

    expect($user->refresh()->email_verified_at->equalTo($verifiedAt))->toBeTrue()
        ->and($user->invitation_token)->toBeNull();
});

test('a confirmed account is not listed as invitation pending even with a stale token', function () {
    $admin = User::factory()->create([
        'is_admin' => true, 'role' => 'superadmin',
        'invitation_token' => hash('sha256', 'stale-invitation'),
        'invitation_expires_at' => now()->addDay(),
    ]);

    $this->actingAs($admin)->get('/admin/users')->assertInertia(fn (Assert $page) => $page
        ->component('Admin/Users/Index', false)
        ->where('users.data.0.email_verified', true)
        ->where('users.data.0.invitation_pending', false));
});
