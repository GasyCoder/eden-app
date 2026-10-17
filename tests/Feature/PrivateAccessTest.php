<?php

use App\Models\ContentBlock;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('private and conventional auth urls show the same custom 404 without revealing the login', function (string $path) {
    $missing = $this->get('/une-page-inexistante')->assertNotFound()->getContent();
    $response = $this->get($path)->assertNotFound()->assertViewIs('errors.404')
        ->assertSee('Page introuvable')->assertDontSee('/me/login', false)
        ->assertHeaderMissing('Location')->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
    expect($response->getContent())->toBe($missing);
})->with(['/admin', '/admin/posts', '/admin/newsletter', '/admin/posts/999/edit', '/admin/login', '/me', '/me/profile', '/login', '/dashboard', '/dashboars', '/register', '/forgot-password']);

test('private routes are concealed for all request methods including unsupported ones', function (string $method) {
    foreach (['/admin', '/admin/posts', '/admin/newsletter/subscribers/999', '/me/profile', '/me/users/999'] as $path) {
        $this->call($method, $path)->assertNotFound()->assertHeaderMissing('Location');
    }
})->with(['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS']);

test('private json endpoints return only a generic not found error', function () {
    $this->getJson('/admin')->assertNotFound()->assertExactJson(['message' => 'Not Found.']);
    $this->postJson('/admin/newsletter/subscribers', [])->assertNotFound()->assertExactJson(['message' => 'Not Found.']);
});

test('private concealment runs before csrf while the login still requires csrf', function () {
    $this->app->detectEnvironment(fn () => 'local');
    $this->post('/admin/posts', [])->assertNotFound();
    $this->post('/me/users', [])->assertNotFound();
    $this->post('/me/login', ['email' => 'unknown@example.test', 'password' => 'wrong'])->assertStatus(419);
});

test('the login page is available only at its intended path and is not indexed or cached', function () {
    ContentBlock::create(['key' => 'seo.analytics_id', 'locale' => 'fr', 'value' => 'G-TEST1234']);
    $login = $this->get('/me/login')->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive')
        ->assertHeader('Referrer-Policy', 'no-referrer');
    expect($login->headers->get('Cache-Control'))->toContain('no-store');
    $login->assertSee('noindex, nofollow, noarchive', false)->assertDontSee('rel="canonical"', false)->assertDontSee('name="eden-analytics"', false);
    $this->get('/')->assertOk()->assertDontSee('/me/login', false);
});

test('an authenticated non admin still sees a 404 for private pages and cannot mutate data', function () {
    $this->actingAs(User::factory()->create(['is_admin' => false]));
    $this->get('/admin')->assertNotFound()->assertHeaderMissing('Location');
    $this->get('/me/profile')->assertNotFound();
    $this->post('/admin/newsletter/subscribers', ['emails' => 'intrus@example.test', 'locale' => 'fr'])->assertNotFound();
    $this->assertDatabaseMissing('newsletter_subscriptions', ['email' => 'intrus@example.test']);
});

test('only administrator accounts can authenticate and credentials use a generic error', function () {
    $account = User::factory()->create(['email' => 'visitor@example.test', 'password' => Hash::make('ExamplePassword123!'), 'is_admin' => false]);
    $this->post('/me/login', ['email' => $account->email, 'password' => 'ExamplePassword123!'])
        ->assertSessionHasErrors(['email' => 'Les identifiants sont incorrects.']);
    $this->assertGuest();
    $this->post('/me/login', ['email' => 'inconnu@example.test', 'password' => 'ExamplePassword123!'])
        ->assertSessionHasErrors(['email' => 'Les identifiants sont incorrects.']);
    $this->assertGuest();
});

test('admin login regenerates the session and logout removes private access', function () {
    $admin = User::factory()->create(['email' => 'admin@example.test', 'password' => Hash::make('ExamplePassword123!'), 'is_admin' => true]);
    $this->get('/me/login');
    $previousId = session()->getId();
    $this->post('/me/login', ['email' => 'ADMIN@example.test', 'password' => 'ExamplePassword123!', 'remember' => true])->assertRedirect('/');
    $this->assertAuthenticatedAs($admin);
    expect(session()->getId())->not->toBe($previousId);
    $private = $this->get('/admin')->assertOk();
    expect($private->headers->get('Cache-Control'))->toContain('no-store');
    $home = $this->get('/')->assertOk();
    expect($home->headers->get('Cache-Control'))->toContain('no-store');
    $this->post('/logout')->assertRedirect('/');
    $this->assertGuest();
    $this->get('/admin')->assertNotFound()->assertHeaderMissing('Location');
});

test('login limits cannot be bypassed by changing email case and expire after a minute', function () {
    $admin = User::factory()->create(['email' => 'admin@example.test', 'password' => Hash::make('ExamplePassword123!'), 'is_admin' => true]);
    for ($i = 0; $i < 5; $i++) {
        $this->post('/me/login', ['email' => $i % 2 ? 'ADMIN@example.test' : 'admin@example.test', 'password' => 'wrong'])->assertSessionHasErrors('email');
    }
    $this->post('/me/login', ['email' => $admin->email, 'password' => 'ExamplePassword123!'])->assertTooManyRequests()->assertHeader('Retry-After');
    $this->assertGuest();
    $this->travel(61)->seconds();
    $this->post('/me/login', ['email' => $admin->email, 'password' => 'ExamplePassword123!'])->assertRedirect();
    $this->assertAuthenticatedAs($admin);
});

test('login also limits attempts from one ip when the email keeps changing', function () {
    for ($i = 0; $i < 20; $i++) {
        $this->post('/me/login', ['email' => "inconnu{$i}@example.test", 'password' => 'wrong'])->assertSessionHasErrors('email');
    }
    $this->post('/me/login', ['email' => 'encore@example.test', 'password' => 'wrong'])->assertTooManyRequests();
    $this->assertGuest();
});

test('malformed login input cannot bypass validation or trigger an exception', function () {
    $this->post('/me/login', ['email' => ['admin@example.test'], 'password' => 'wrong'])->assertSessionHasErrors('email');
    $this->post('/me/login', ['email' => 'admin@example.test', 'password' => ['wrong']])->assertSessionHasErrors('password');
    $this->assertGuest();
});
