<?php

use App\Models\Journal;
use App\Models\JournalIssue;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

function issueEditor(): User
{
    return User::factory()->create(['is_admin' => true, 'role' => 'editor']);
}

test('journal page lists published issues only', function () {
    $journal = Journal::factory()->create(['slug' => 'matoy']);
    $published = JournalIssue::factory()->for($journal)->create(['number' => 2]);
    JournalIssue::factory()->for($journal)->draft()->create(['number' => 3]);
    JournalIssue::factory()->for($journal)->create(['number' => 4, 'published_at' => now()->addWeek()]);

    $this->get('/revues/matoy')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Journals/Show', false)
            ->has('issues', 1)
            ->where('issues.0.id', $published->id)
            ->where('issues.0.label', 'N° 02 · 2026'));
});

test('issue page shows contents grouped by rubric, stats and localized urls', function () {
    $journal = Journal::factory()->create(['slug' => 'matoy']);
    $issue = JournalIssue::factory()->for($journal)->create([
        'slug' => 'n01-2026',
        'number' => 1,
        'sections' => [
            ['rubric' => 'Soutenances', 'title' => 'Thèse A'],
            ['rubric' => 'Actualités', 'title' => 'Atelier'],
            ['rubric' => 'Soutenances', 'title' => 'HDR B'],
        ],
    ]);

    $this->get('/en/journals/matoy/n01-2026')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Journals/Issue', false)
            ->where('issue.id', $issue->id)
            ->where('issue.contents.0.rubric', 'Soutenances')
            ->has('issue.contents.0.items', 2)
            ->has('issue.stats', 2)
            ->where('languageUrls.fr', route('journals.issues.show', ['matoy', 'n01-2026'])));
});

test('draft issues are hidden from visitors but previewable by admins', function () {
    $journal = Journal::factory()->create(['slug' => 'matoy']);
    JournalIssue::factory()->for($journal)->draft()->create(['slug' => 'n05-2026']);

    $this->get('/revues/matoy/n05-2026')->assertNotFound();
    $this->actingAs(issueEditor())->get('/revues/matoy/n05-2026')->assertOk();
});

test('an issue slug is scoped to its journal', function () {
    $other = Journal::factory()->create(['slug' => 'autre']);
    Journal::factory()->create(['slug' => 'matoy']);
    JournalIssue::factory()->for($other)->create(['slug' => 'n01-2026']);

    $this->get('/revues/matoy/n01-2026')->assertNotFound();
});

test('homepage receives the latest published issue', function () {
    $journal = Journal::factory()->create();
    JournalIssue::factory()->for($journal)->create(['number' => 1, 'published_at' => now()->subMonths(3)]);
    $latest = JournalIssue::factory()->for($journal)->create(['number' => 2, 'published_at' => now()->subDay()]);

    $this->get('/')
        ->assertInertia(fn (Assert $page) => $page->where('latestIssue.id', $latest->id)->where('latestIssue.journal.slug', $journal->slug));
});

test('admins create an issue with contents and stats', function () {
    $journal = Journal::factory()->create();

    $this->actingAs(issueEditor())->post("/admin/journals/{$journal->id}/issues", [
        'number' => 1,
        'year' => 2026,
        'period' => ['fr' => 'Juin - Août'],
        'editorial' => ['fr' => '<p>Éditorial<script>alert(1)</script></p>'],
        'pdf_url' => '/storage/documents/matoy.pdf',
        'sections' => [['rubric' => 'Actualités', 'title' => 'Atelier D4R', 'summary' => 'Résumé']],
        'stats' => [['label' => 'Communication', 'value' => 13]],
        'status' => 'published',
        'published_at' => '2026-09-01',
    ])->assertRedirect();

    $issue = JournalIssue::firstOrFail();
    expect($issue->slug)->toBe('n01-2026')
        ->and($issue->editorial['fr'])->not->toContain('script')
        ->and($issue->sections[0]['title'])->toBe('Atelier D4R')
        ->and($issue->stats[0]['value'])->toEqual(13)
        ->and($issue->published_at->toDateString())->toBe('2026-08-31'); // minuit à Madagascar = 21 h UTC la veille
});

test('issue numbers are unique per journal and year', function () {
    $journal = Journal::factory()->create();
    JournalIssue::factory()->for($journal)->create(['number' => 1, 'year' => 2026]);

    $this->actingAs(issueEditor())->post("/admin/journals/{$journal->id}/issues", [
        'number' => 1,
        'year' => 2026,
        'status' => 'draft',
    ])->assertSessionHasErrors('number');
});

test('admin issue screens open and are scoped to the journal', function () {
    $journal = Journal::factory()->create();
    $issue = JournalIssue::factory()->for($journal)->create();
    $other = JournalIssue::factory()->create();
    $editor = issueEditor();

    $this->actingAs($editor)->get("/admin/journals/{$journal->id}/issues")
        ->assertOk()->assertInertia(fn (Assert $page) => $page->component('Admin/Journals/Issues/Index', false)->has('issues', 1));
    $this->actingAs($editor)->get("/admin/journals/{$journal->id}/issues/create")
        ->assertOk()->assertInertia(fn (Assert $page) => $page->component('Admin/Journals/Issues/Form', false));
    $this->actingAs($editor)->get("/admin/journals/{$journal->id}/issues/{$issue->id}/edit")->assertOk();
    $this->actingAs($editor)->get("/admin/journals/{$journal->id}/issues/{$other->id}/edit")->assertNotFound();
});

test('pdf upload accepts pdf files only and requires admin rights', function () {
    Storage::fake('public');
    $editor = issueEditor();

    $this->actingAs($editor)->postJson('/admin/uploads/documents', [
        'document' => UploadedFile::fake()->create('matoy.pdf', 200, 'application/pdf'),
    ])->assertOk()->assertJsonStructure(['path', 'size']);

    $this->actingAs($editor)->postJson('/admin/uploads/documents', [
        'document' => UploadedFile::fake()->create('virus.exe', 10, 'application/octet-stream'),
    ])->assertUnprocessable()->assertJsonPath('message', 'Seuls les fichiers PDF sont acceptés.');

    $this->actingAs(User::factory()->create(['is_admin' => false]))->postJson('/admin/uploads/documents', [
        'document' => UploadedFile::fake()->create('matoy.pdf', 10, 'application/pdf'),
    ])->assertForbidden();
});
