<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesEditorialInput;
use App\Http\Controllers\Admin\Concerns\NotifiesSubscribers;
use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Models\JournalIssue;
use App\Services\ContentSanitizer;
use App\Support\EditorialDate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Numéros d'une revue : MaToY (journal trimestriel) ou toute autre revue du catalogue.
 */
class JournalIssueController extends Controller
{
    use HandlesEditorialInput, NotifiesSubscribers;

    public function index(Journal $journal): Response
    {
        return Inertia::render('Admin/Journals/Issues/Index', [
            'journal' => $this->journalSummary($journal),
            'issues' => $journal->issues()->latestFirst()->get()
                ->map(fn (JournalIssue $issue) => $issue->toAdminRow())->values()->all(),
        ]);
    }

    public function create(Journal $journal): Response
    {
        $last = $journal->issues()->latestFirst()->first();
        $year = (int) now()->timezone(EditorialDate::timezone())->format('Y');

        $issue = new JournalIssue([
            'number' => $last && $last->year === $year ? $last->number + 1 : 1,
            'year' => $year,
            'status' => 'draft',
            // Reprend les rubriques et indicateurs du numéro précédent pour gagner du temps
            'stats' => collect($last?->stats ?? [])->map(fn ($stat) => ['label' => $stat['label'] ?? '', 'value' => null])->all(),
        ]);

        return Inertia::render('Admin/Journals/Issues/Form', [
            'journal' => $this->journalSummary($journal),
            'issue' => $issue->toForm(),
            'rubrics' => JournalIssue::RUBRICS,
            'newsletter' => $this->newsletterState(),
        ]);
    }

    public function store(Request $request, Journal $journal): RedirectResponse
    {
        $issue = $journal->issues()->create($this->payload($request, $journal));
        $issue->setRelation('journal', $journal);

        return to_route('admin.journals.issues.edit', [$journal, $issue])->with('success', 'Numéro enregistré.'.$this->notifySubscribers($request, $issue));
    }

    public function edit(Journal $journal, JournalIssue $issue): Response
    {
        return Inertia::render('Admin/Journals/Issues/Form', [
            'journal' => $this->journalSummary($journal),
            'issue' => $issue->toForm(),
            'rubrics' => JournalIssue::RUBRICS,
            'publicUrl' => route('journals.issues.show', [$journal->slug, $issue->slug]),
            'newsletter' => $this->newsletterState($issue),
        ]);
    }

    public function update(Request $request, Journal $journal, JournalIssue $issue): RedirectResponse
    {
        $issue->update($this->payload($request, $journal, $issue));
        $issue->setRelation('journal', $journal);

        return back(303)->with('success', 'Numéro enregistré.'.$this->notifySubscribers($request, $issue));
    }

    public function destroy(Journal $journal, JournalIssue $issue): RedirectResponse
    {
        $issue->delete();

        return to_route('admin.journals.issues.index', $journal)->with('success', 'Numéro supprimé.');
    }

    private function journalSummary(Journal $journal): array
    {
        return [
            'id' => $journal->id,
            'slug' => $journal->slug,
            'title' => $journal->translate('title', 'fr'),
            'frequency' => $journal->translate('frequency', 'fr'),
            'cover_image' => $journal->cover_image,
        ];
    }

    private function payload(Request $request, Journal $journal, ?JournalIssue $issue = null): array
    {
        $data = $request->validate([
            'number' => ['required', 'integer', 'min:1', 'max:9999'],
            'year' => ['required', 'integer', 'min:1990', 'max:2100'],
            ...$this->translatableRules('period', max: 80),
            ...$this->translatableRules('title', max: 190),
            ...$this->translatableRules('editorial', html: true),
            'slug' => ['nullable', 'string', 'max:190', 'alpha_dash'],
            'cover_image' => ['nullable', 'string', 'max:2048'],
            'pdf_url' => ['nullable', 'string', 'max:2048', 'regex:/^(https?:\/\/|\/)/i'],
            'pdf_size' => ['nullable', 'integer', 'min:0'],
            'page_count' => ['nullable', 'integer', 'min:1', 'max:2000'],
            'sections' => ['nullable', 'array', 'max:60'],
            'sections.*.rubric' => ['nullable', 'string', 'max:80'],
            'sections.*.title' => ['required', 'string', 'max:255'],
            'sections.*.summary' => ['nullable', 'string', 'max:3000'],
            'sections.*.image' => ['nullable', 'string', 'max:2048'],
            'sections.*.page' => ['nullable', 'integer', 'min:1', 'max:2000'],
            'stats' => ['nullable', 'array', 'max:20'],
            'stats.*.label' => ['required', 'string', 'max:80'],
            'stats.*.value' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'status' => ['required', Rule::in(JournalIssue::STATUSES)],
            'published_at' => ['nullable', 'date_format:Y-m-d'],
        ], [
            'sections.*.title.required' => 'Chaque article du sommaire doit avoir un titre.',
            'stats.*.label.required' => 'Chaque indicateur doit avoir un libellé.',
            'stats.*.value.required' => 'Indiquez une valeur pour chaque indicateur.',
            'pdf_url.regex' => 'Lien PDF attendu : fichier téléversé ou adresse https://.',
        ]);

        $duplicate = $journal->issues()->withTrashed()
            ->where('number', $data['number'])->where('year', $data['year'])
            ->when($issue, fn ($query) => $query->whereKeyNot($issue->id))
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'number' => 'Ce numéro existe déjà pour cette année.',
            ]);
        }

        $publishedAt = filled($data['published_at'] ?? null)
            ? Carbon::parse($data['published_at'], EditorialDate::timezone())->startOfDay()->utc()
            : null;

        // Publier sans date = publier maintenant
        if ($data['status'] === 'published' && ! $publishedAt) {
            $publishedAt = $issue?->published_at ?? now();
        }

        return [
            'number' => (int) $data['number'],
            'year' => (int) $data['year'],
            'period' => $this->translated($data, 'period'),
            'title' => $this->translated($data, 'title'),
            'editorial' => $this->translated($data, 'editorial', 'html'),
            'slug' => $this->issueSlug($journal, $data, $issue),
            'cover_image' => $this->plain($data['cover_image'] ?? null, 'image'),
            'pdf_url' => $this->plain($data['pdf_url'] ?? null, 'link'),
            'pdf_size' => $data['pdf_size'] ?? null,
            'page_count' => $data['page_count'] ?? null,
            'sections' => collect($data['sections'] ?? [])->map(fn ($section) => array_filter([
                'rubric' => $this->plain($section['rubric'] ?? null),
                'title' => $this->plain($section['title']),
                'summary' => app(ContentSanitizer::class)->clean($section['summary'] ?? null, 'textarea') ?: null,
                'image' => $this->plain($section['image'] ?? null, 'image'),
                'page' => isset($section['page']) ? (int) $section['page'] : null,
            ], fn ($value) => $value !== null && $value !== ''))->values()->all() ?: null,
            'stats' => collect($data['stats'] ?? [])->map(fn ($stat) => [
                'label' => $this->plain($stat['label']),
                'value' => (float) $stat['value'] + 0,
            ])->values()->all() ?: null,
            'status' => $data['status'],
            'published_at' => $publishedAt,
        ];
    }

    private function issueSlug(Journal $journal, array $data, ?JournalIssue $issue): string
    {
        $base = Str::slug($data['slug'] ?? '') ?: sprintf('n%02d-%d', $data['number'], $data['year']);
        $slug = $base;
        $suffix = 2;

        while ($journal->issues()->withTrashed()->where('slug', $slug)->when($issue, fn ($query) => $query->whereKeyNot($issue->id))->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
