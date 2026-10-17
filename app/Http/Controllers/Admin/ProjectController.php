<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesBulkActions;
use App\Http\Controllers\Admin\Concerns\HandlesEditorialInput;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\ContentSanitizer;
use App\Support\Paginated;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    use HandlesBulkActions, HandlesEditorialInput;

    public function index(Request $request): Response
    {
        $search = trim($request->string('q')->toString());
        $status = $request->string('status')->toString();
        $progress = $request->string('progress')->toString();

        $projects = Project::query()
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('title->fr', 'like', "%{$search}%")
                ->orWhere('title->en', 'like', "%{$search}%")
                ->orWhere('acronym', 'like', "%{$search}%")
                ->orWhere('funder', 'like', "%{$search}%")))
            ->when(in_array($status, Project::STATUSES, true), fn (Builder $query) => $query->where('status', $status))
            ->when(in_array($progress, Project::PROGRESS, true), fn (Builder $query) => $query->where('progress', $progress))
            ->ordered()
            ->paginate(20);

        return Inertia::render('Admin/Projects/Index', [
            'projects' => Paginated::from($projects, fn (Project $project) => $project->toAdminRow()),
            'filters' => ['q' => $search, 'status' => $status, 'progress' => $progress],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Projects/Form', [
            'project' => (new Project([
                'status' => 'draft',
                'progress' => 'ongoing',
                'is_featured' => false,
                'position' => (int) Project::query()->max('position') + 1,
            ]))->toForm(),
            'teams' => $this->teams(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $project = Project::create($this->payload($request));

        return to_route('admin.projects.edit', $project)->with('success', 'Projet enregistré.');
    }

    public function edit(Project $project): Response
    {
        return Inertia::render('Admin/Projects/Form', [
            'project' => $project->toForm(),
            'teams' => $this->teams(),
            'publicUrl' => $project->hasDetailPage() ? route('projects.show', $project->slug) : null,
        ]);
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $project->update($this->payload($request, $project));

        return back(303)->with('success', 'Projet enregistré.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return to_route('admin.projects.index')->with('success', 'Projet supprimé.');
    }

    /** Ordre d'affichage par glisser (liste d'identifiants). */
    public function reorder(Request $request): RedirectResponse
    {
        $ids = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']])['ids'];

        foreach (array_values($ids) as $position => $id) {
            Project::query()->whereKey($id)->update(['position' => $position]);
        }

        return back(303)->with('success', 'Ordre enregistré.');
    }

    private function payload(Request $request, ?Project $project = null): array
    {
        $data = $request->validate([
            ...$this->translatableRules('title', required: true),
            ...$this->translatableRules('subtitle', max: 255),
            ...$this->translatableRules('summary', max: 1200),
            ...$this->translatableRules('body', html: true),
            'slug' => ['nullable', 'string', 'max:190', 'alpha_dash'],
            'acronym' => ['nullable', 'string', 'max:60'],
            'cover_image' => ['nullable', 'string', 'max:2048'],
            'logo' => ['nullable', 'string', 'max:2048'],
            'stage' => ['required', Rule::in(Project::PROGRESS)],
            'team' => ['nullable', 'string', 'max:120'],
            'funder' => ['nullable', 'string', 'max:255'],
            'leader' => ['nullable', 'string', 'max:255'],
            'start_year' => ['nullable', 'integer', 'min:1990', 'max:2100'],
            'end_year' => ['nullable', 'integer', 'min:1990', 'max:2100', 'gte:start_year'],
            'external_url' => ['nullable', 'url:http,https', 'max:2048'],
            'objectives' => ['nullable', 'array', 'max:30'],
            'objectives.*' => ['nullable', 'string', 'max:500'],
            'beneficiaries' => ['nullable', 'array', 'max:30'],
            'beneficiaries.*' => ['nullable', 'string', 'max:500'],
            'partners' => ['nullable', 'array', 'max:40'],
            'partners.*.name' => ['required', 'string', 'max:255'],
            'partners.*.country' => ['nullable', 'string', 'max:120'],
            'partners.*.role' => ['nullable', 'string', 'max:255'],
            'partners.*.logo' => ['nullable', 'string', 'max:2048'],
            'partners.*.url' => ['nullable', 'url:http,https', 'max:2048'],
            'work_packages' => ['nullable', 'array', 'max:20'],
            'work_packages.*.code' => ['nullable', 'string', 'max:20'],
            'work_packages.*.title' => ['required', 'string', 'max:255'],
            'work_packages.*.description' => ['nullable', 'string', 'max:2000'],
            'work_packages.*.lead' => ['nullable', 'string', 'max:255'],
            'facts' => ['nullable', 'array', 'max:12'],
            'facts.*.label' => ['required', 'string', 'max:80'],
            'facts.*.value' => ['required', 'string', 'max:80'],
            'funding_notice' => ['nullable', 'string', 'max:2000'],
            'funding_logo' => ['nullable', 'string', 'max:2048'],
            'gallery_image' => ['nullable', 'string', 'max:2048'],
            'status' => ['required', Rule::in(Project::STATUSES)],
            'is_featured' => ['boolean'],
            'position' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ], [
            'title.fr.required' => 'Le titre en français est obligatoire.',
            'end_year.gte' => 'L’année de fin doit suivre l’année de début.',
            'partners.*.name.required' => 'Chaque partenaire doit avoir un nom.',
            'work_packages.*.title.required' => 'Chaque lot de travail doit avoir un titre.',
            'facts.*.label.required' => 'Chaque chiffre clé doit avoir un libellé.',
            'facts.*.value.required' => 'Chaque chiffre clé doit avoir une valeur.',
            'external_url.url' => 'Indiquez une adresse complète commençant par https://.',
            'partners.*.url.url' => 'Indiquez une adresse complète commençant par https://.',
        ]);

        $sanitizer = app(ContentSanitizer::class);
        $text = fn ($value, string $type = 'text') => ($clean = $sanitizer->clean($value, $type)) === '' ? null : $clean;
        $lines = fn (array $values) => array_values(array_filter(array_map(fn ($value) => $text($value), $values)));

        $details = array_filter([
            'objectives' => $lines($data['objectives'] ?? []),
            'beneficiaries' => $lines($data['beneficiaries'] ?? []),
            'partners' => array_values(array_map(fn ($partner) => array_filter([
                'name' => $text($partner['name']),
                'country' => $text($partner['country'] ?? null),
                'role' => $text($partner['role'] ?? null),
                'logo' => $text($partner['logo'] ?? null, 'image'),
                'url' => $text($partner['url'] ?? null, 'link'),
            ]), $data['partners'] ?? [])),
            'work_packages' => array_values(array_map(fn ($package) => array_filter([
                'code' => $text($package['code'] ?? null),
                'title' => $text($package['title']),
                'description' => $text($package['description'] ?? null, 'textarea'),
                'lead' => $text($package['lead'] ?? null),
            ]), $data['work_packages'] ?? [])),
            'facts' => array_values(array_map(fn ($fact) => ['label' => $text($fact['label']), 'value' => $text($fact['value'])], $data['facts'] ?? [])),
            'funding_notice' => $text($data['funding_notice'] ?? null, 'textarea'),
            'funding_logo' => $text($data['funding_logo'] ?? null, 'image'),
            'gallery_image' => $text($data['gallery_image'] ?? null, 'image'),
        ], fn ($value) => ! empty($value));

        return [
            'title' => $this->translated($data, 'title'),
            'subtitle' => $this->translated($data, 'subtitle'),
            'summary' => $this->translated($data, 'summary', 'textarea'),
            'body' => $this->translated($data, 'body', 'html'),
            'slug' => $this->uniqueSlug(Project::class, $data['slug'] ?? null, $data['acronym'] ?? $data['title']['fr'] ?? null, $project),
            'acronym' => $this->plain($data['acronym'] ?? null),
            'cover_image' => $this->plain($data['cover_image'] ?? null, 'image'),
            'logo' => $this->plain($data['logo'] ?? null, 'image'),
            'progress' => $data['stage'],
            'team' => $this->plain($data['team'] ?? null),
            'funder' => $this->plain($data['funder'] ?? null),
            'leader' => $this->plain($data['leader'] ?? null),
            'start_year' => $data['start_year'] ?? null,
            'end_year' => $data['end_year'] ?? null,
            'external_url' => $this->plain($data['external_url'] ?? null, 'link'),
            'details' => $details ?: null,
            'status' => $data['status'],
            'is_featured' => (bool) ($data['is_featured'] ?? false),
            'position' => (int) ($data['position'] ?? 0),
        ];
    }

    private function teams(): array
    {
        return Project::query()->whereNotNull('team')->distinct()->orderBy('team')->pluck('team')->values()->all();
    }

    protected function bulkModel(): string
    {
        return Project::class;
    }

    protected function bulkActions(): array
    {
        return [
            'publish' => ['%d projet(s) publié(s).', fn (Collection $projects) => $this->updateEach($projects, ['status' => 'published'])],
            'draft' => ['%d projet(s) repassé(s) en brouillon.', fn (Collection $projects) => $this->updateEach($projects, ['status' => 'draft'])],
            'feature' => ['%d projet(s) mis en avant.', fn (Collection $projects) => $this->updateEach($projects, ['is_featured' => true])],
            'unfeature' => ['%d projet(s) retiré(s) de la mise en avant.', fn (Collection $projects) => $this->updateEach($projects, ['is_featured' => false])],
            'delete' => ['%d projet(s) supprimé(s).', fn (Collection $projects) => $this->deleteEach($projects)],
        ];
    }
}
