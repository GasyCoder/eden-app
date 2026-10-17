<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesBulkActions;
use App\Http\Controllers\Admin\Concerns\HandlesEditorialInput;
use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Support\Paginated;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class JournalController extends Controller
{
    use HandlesBulkActions, HandlesEditorialInput;

    public function index(Request $request): Response
    {
        $search = trim($request->string('q')->toString());
        $status = $request->string('status')->toString();

        $journals = Journal::query()->withCount('issues')
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('title->fr', 'like', "%{$search}%")
                ->orWhere('title->en', 'like', "%{$search}%")
                ->orWhere('issn', 'like', "%{$search}%")
                ->orWhere('domain', 'like', "%{$search}%")))
            ->when(in_array($status, Journal::STATUSES, true), fn (Builder $query) => $query->where('status', $status))
            ->ordered()
            ->paginate(15);

        return Inertia::render('Admin/Journals/Index', [
            'journals' => Paginated::from($journals, fn (Journal $journal) => $journal->toAdminRow()),
            'filters' => ['q' => $search, 'status' => $status],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Journals/Form', [
            'journal' => (new Journal(['status' => 'draft', 'is_featured' => false, 'position' => 0]))->toForm(),
            'domains' => $this->domains(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $journal = Journal::create($this->payload($request));

        return to_route('admin.journals.edit', $journal)->with('success', 'Revue enregistrée.');
    }

    public function edit(Journal $journal): Response
    {
        return Inertia::render('Admin/Journals/Form', [
            'journal' => $journal->toForm(),
            'domains' => $this->domains(),
            'publicUrl' => route('journals.show', $journal->slug),
            'issuesCount' => $journal->issues()->count(),
        ]);
    }

    public function update(Request $request, Journal $journal): RedirectResponse
    {
        $journal->update($this->payload($request, $journal));

        return back(303)->with('success', 'Revue enregistrée.');
    }

    public function destroy(Journal $journal): RedirectResponse
    {
        $journal->delete();

        return to_route('admin.journals.index')->with('success', 'Revue supprimée.');
    }

    private function payload(Request $request, ?Journal $journal = null): array
    {
        $data = $request->validate([
            ...$this->translatableRules('title', required: true),
            ...$this->translatableRules('description', html: true),
            ...$this->translatableRules('frequency', max: 80),
            'slug' => ['nullable', 'string', 'max:190', 'alpha_dash'],
            'domain' => ['nullable', 'string', 'max:120'],
            'cover_image' => ['nullable', 'string', 'max:2048'],
            'issn' => ['nullable', 'string', 'max:20', 'regex:/^\d{4}-?\d{3}[\dXx]$/'],
            'external_url' => ['nullable', 'url:http,https', 'max:2048'],
            'editor' => ['nullable', 'string', 'max:255'],
            'language' => ['nullable', 'string', 'max:60'],
            'status' => ['required', Rule::in(Journal::STATUSES)],
            'is_featured' => ['boolean'],
            'position' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ], [
            'title.fr.required' => 'Le titre en français est obligatoire.',
            'issn.regex' => 'Format ISSN attendu : 1234-5678.',
            'external_url.url' => 'Indiquez une adresse complète commençant par https://.',
        ]);

        $issn = $data['issn'] ?? null;

        return [
            'title' => $this->translated($data, 'title'),
            'description' => $this->translated($data, 'description', 'html'),
            'frequency' => $this->translated($data, 'frequency'),
            'slug' => $this->uniqueSlug(Journal::class, $data['slug'] ?? null, $data['title']['fr'] ?? null, $journal),
            'domain' => $this->plain($data['domain'] ?? null),
            'cover_image' => $this->plain($data['cover_image'] ?? null, 'image'),
            'issn' => $issn ? strtoupper(substr(str_replace('-', '', $issn), 0, 4).'-'.substr(str_replace('-', '', $issn), 4)) : null,
            'external_url' => $this->plain($data['external_url'] ?? null, 'link'),
            'editor' => $this->plain($data['editor'] ?? null),
            'language' => $this->plain($data['language'] ?? null),
            'status' => $data['status'],
            'is_featured' => (bool) ($data['is_featured'] ?? false),
            'position' => (int) ($data['position'] ?? 0),
        ];
    }

    private function domains(): array
    {
        return Journal::query()->whereNotNull('domain')->distinct()->orderBy('domain')->pluck('domain')->values()->all();
    }

    protected function bulkModel(): string
    {
        return Journal::class;
    }

    protected function bulkActions(): array
    {
        return [
            'activate' => ['%d revue(s) activée(s).', fn (Collection $journals) => $this->updateEach($journals, ['status' => 'active'])],
            'archive' => ['%d revue(s) archivée(s).', fn (Collection $journals) => $this->updateEach($journals, ['status' => 'archived'])],
            'draft' => ['%d revue(s) repassée(s) en brouillon.', fn (Collection $journals) => $this->updateEach($journals, ['status' => 'draft'])],
            'feature' => ['%d revue(s) mise(s) en avant.', fn (Collection $journals) => $this->updateEach($journals, ['is_featured' => true])],
            'unfeature' => ['%d revue(s) retirée(s) de l’accueil.', fn (Collection $journals) => $this->updateEach($journals, ['is_featured' => false])],
            'delete' => ['%d revue(s) supprimée(s).', fn (Collection $journals) => $this->deleteEach($journals)],
        ];
    }
}
