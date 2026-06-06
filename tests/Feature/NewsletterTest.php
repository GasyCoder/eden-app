<?php

use App\Models\NewsletterSubscription;

test('guest can subscribe to newsletter', function () {
    $this->post('/newsletter', [
        'email' => 'Doctorant@Example.COM',
    ])
        ->assertRedirect('/newsletter/confirmation')
        ->assertSessionHas('newsletter_email', 'doctorant@example.com');

    expect(NewsletterSubscription::where('email', 'doctorant@example.com')->exists())->toBeTrue();
});

test('newsletter email is required and valid', function () {
    $this->post('/newsletter', [
        'email' => 'adresse-invalide',
    ])->assertSessionHasErrors('email');
});

test('newsletter confirmation requires submitted email session', function () {
    $this->get('/newsletter/confirmation')
        ->assertRedirect('/');
});

test('newsletter confirmation page displays submitted email', function () {
    $this->withSession(['newsletter_email' => 'chercheur@example.com'])
        ->get('/newsletter/confirmation')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('email', 'chercheur@example.com'));
});
