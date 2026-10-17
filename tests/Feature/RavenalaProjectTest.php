<?php

use Inertia\Testing\AssertableInertia as Assert;

test('ravenala project page is rendered from the projects table', function () {
    $this->get('/projets/ravenala')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Projects/Show', false)
            ->where('project.slug', 'ravenala')
            ->has('project.partners', 5)
            ->has('project.work_packages', 5)
            ->where('languageUrls.en', route('projects.show.en', 'ravenala')));
});

test('short ravenala url redirects to project page', function () {
    $this->get('/ravenala')
        ->assertRedirect(route('projects.show', 'ravenala'));
});
