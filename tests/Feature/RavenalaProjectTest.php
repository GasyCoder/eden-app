<?php

use Inertia\Testing\AssertableInertia as Assert;

test('ravenala project page is rendered', function () {
    $this->get('/projets/ravenala')
        ->assertOk()
        ->assertSee('app-skeleton')
        ->assertInertia(fn (Assert $page) => $page
            ->component('Projets/Ravenala', false)
            ->where('locale', 'fr')
            ->where('languageUrls.fr', route('projects.ravenala')));
});

test('short ravenala url redirects to project page', function () {
    $this->get('/ravenala')
        ->assertRedirect(route('projects.ravenala'));
});
