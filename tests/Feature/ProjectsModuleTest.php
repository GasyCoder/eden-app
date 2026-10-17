<?php

use App\Models\Project;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

function projectEditor(): User
{
    return User::factory()->create(['is_admin' => true, 'role' => 'editor']);
}

test('public project list shows published projects and filters by progress', function () {
    Project::query()->delete();
    $ongoing = Project::factory()->create(['progress' => 'ongoing']);
    Project::factory()->create(['progress' => 'completed']);
    Project::factory()->draft()->create();

    $this->get('/projets')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Projects/Index', false)->has('projects', 2));

    $this->get('/en/projects?etat=ongoing')
        ->assertInertia(fn (Assert $page) => $page->has('projects', 1)->where('projects.0.id', $ongoing->id));
});

test('a project card links to its page when it has details, otherwise to its website', function () {
    $withPage = Project::factory()->withPage()->create(['slug' => 'avec-page']);
    $external = Project::factory()->create(['external_url' => 'https://example.org/projet']);

    expect($withPage->toCard('fr')['url'])->toBe('/projets/avec-page')
        ->and($withPage->toCard('en')['url'])->toBe('/en/projects/avec-page')
        ->and($external->toCard('fr'))->toMatchArray(['url' => 'https://example.org/projet', 'is_external' => true]);
});

test('draft project pages are hidden from visitors but previewable by admins', function () {
    $project = Project::factory()->draft()->withPage()->create();

    $this->get("/projets/{$project->slug}")->assertNotFound();
    $this->actingAs(projectEditor())->get("/projets/{$project->slug}")->assertOk();
});

test('home and research pages receive published projects', function () {
    Project::factory()->create();

    $this->get('/')->assertInertia(fn (Assert $page) => $page->has('projects'));
    $this->get('/recherche')->assertInertia(fn (Assert $page) => $page->has('projects', Project::published()->count()));
});

test('admins create a project with its detail page blocks', function () {
    $this->actingAs(projectEditor())->post('/admin/projects', [
        'title' => ['fr' => 'Projet MANGROVES'],
        'acronym' => 'MANGROVES',
        'summary' => ['fr' => 'Restauration des mangroves.'],
        'stage' => 'upcoming',
        'start_year' => 2026,
        'end_year' => 2029,
        'objectives' => ['Cartographier', '', 'Restaurer'],
        'partners' => [['name' => 'Université de Mahajanga', 'country' => 'Madagascar', 'url' => 'https://www.univ-mahajanga.mg']],
        'work_packages' => [['code' => 'WP1', 'title' => 'Coordination']],
        'facts' => [['label' => 'Partenaires', 'value' => '3']],
        'status' => 'published',
    ])->assertRedirect();

    $project = Project::query()->where('acronym', 'MANGROVES')->firstOrFail();

    expect($project->slug)->toBe('mangroves')
        ->and($project->detail('objectives'))->toBe(['Cartographier', 'Restaurer'])
        ->and($project->hasDetailPage())->toBeTrue()
        ->and($project->period())->toBe('2026 - 2029');
});

test('project end year cannot precede start year', function () {
    $this->actingAs(projectEditor())->post('/admin/projects', [
        'title' => ['fr' => 'Projet'],
        'stage' => 'ongoing',
        'start_year' => 2027,
        'end_year' => 2025,
        'status' => 'draft',
    ])->assertSessionHasErrors('end_year');
});

test('admin project screens open and projects can be reordered', function () {
    $editor = projectEditor();
    $first = Project::factory()->create(['position' => 0]);
    $second = Project::factory()->create(['position' => 1]);

    $this->actingAs($editor)->get('/admin/projects')->assertOk()->assertInertia(fn (Assert $page) => $page->component('Admin/Projects/Index', false));
    $this->actingAs($editor)->get('/admin/projects/create')->assertOk();
    $this->actingAs($editor)->get("/admin/projects/{$first->id}/edit")->assertOk()->assertInertia(fn (Assert $page) => $page->component('Admin/Projects/Form', false));

    $this->actingAs($editor)->patch('/admin/projects/reorder', ['ids' => [$second->id, $first->id]])->assertRedirect();
    expect($second->fresh()->position)->toBe(0)->and($first->fresh()->position)->toBe(1);
});
