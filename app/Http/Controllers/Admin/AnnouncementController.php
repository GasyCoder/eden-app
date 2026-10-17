<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesBulkActions;
use App\Http\Controllers\Admin\Concerns\HandlesEditorialInput;
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Support\EditorialDate;
use App\Support\Paginated;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AnnouncementController extends Controller
{
    use HandlesBulkActions, HandlesEditorialInput;

    public function index(Request $request): Response
    {
        $search = trim($request->string('q')->toString());
        $status = $request->string('status')->toString();
        $type = $request->string('type')->toString();

        $announcements = Announcement::query()
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('title->fr', 'like', "%{$search}%")
                ->orWhere('title->en', 'like', "%{$search}%")))
            ->when(in_array($status, Announcement::STATUSES, true), fn (Builder $query) => $query->where('status', $status))
            ->when(in_array($type, Announcement::TYPES, true), fn (Builder $query) => $query->where('type', $type))
            ->orderByRaw("case when status = 'published' then 0 when status = 'draft' then 1 else 2 end")
            ->orderByDesc('priority')
            ->latest('updated_at')
            ->paginate(15);

        return Inertia::render('Admin/Announcements/Index', [
            'announcements' => Paginated::from($announcements, fn (Announcement $announcement) => $announcement->toAdminRow()),
            'filters' => ['q' => $search, 'status' => $status, 'type' => $type],
            'displayByType' => Announcement::DISPLAY,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Announcements/Form', [
            'announcement' => (new Announcement(['type' => 'info', 'status' => 'draft', 'priority' => 1]))->toForm(),
            'displayByType' => Announcement::DISPLAY,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $announcement = Announcement::create($this->payload($request));

        return to_route('admin.announcements.edit', $announcement)->with('success', 'Annonce enregistrée.');
    }

    public function edit(Announcement $announcement): Response
    {
        return Inertia::render('Admin/Announcements/Form', [
            'announcement' => $announcement->toForm(),
            'displayByType' => Announcement::DISPLAY,
            'state' => $announcement->state(),
        ]);
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $announcement->update($this->payload($request));

        return back(303)->with('success', 'Annonce enregistrée.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        return to_route('admin.announcements.index')->with('success', 'Annonce supprimée.');
    }

    private function payload(Request $request): array
    {
        $data = $request->validate([
            ...$this->translatableRules('title', required: true, max: 160),
            ...$this->translatableRules('message', max: 1000),
            ...$this->translatableRules('cta_label', max: 60),
            'cta_url' => $this->linkRule(),
            'image' => ['nullable', 'string', 'max:2048'],
            'type' => ['required', Rule::in(Announcement::TYPES)],
            'status' => ['required', Rule::in(Announcement::STATUSES)],
            'priority' => ['required', 'integer', 'between:0,2'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => array_filter(['nullable', 'date', $request->filled('starts_at') ? 'after:starts_at' : null]),
        ], [
            'title.fr.required' => 'Le titre en français est obligatoire.',
            'ends_at.after' => 'La fin de diffusion doit être postérieure au début.',
            'cta_url.regex' => 'Le lien doit commencer par https://, / ou #.',
        ]);

        return [
            'title' => $this->translated($data, 'title'),
            'message' => $this->translated($data, 'message', 'textarea'),
            'cta_label' => $this->translated($data, 'cta_label'),
            'cta_url' => $this->plain($data['cta_url'] ?? null, 'link'),
            'image' => $this->plain($data['image'] ?? null, 'image'),
            'type' => $data['type'],
            'status' => $data['status'],
            'priority' => (int) $data['priority'],
            'starts_at' => EditorialDate::fromInput($data['starts_at'] ?? null),
            'ends_at' => EditorialDate::fromInput($data['ends_at'] ?? null),
        ];
    }

    protected function bulkModel(): string
    {
        return Announcement::class;
    }

    protected function bulkActions(): array
    {
        return [
            'publish' => ['%d annonce(s) publiée(s).', fn (Collection $items) => $this->updateEach($items, ['status' => 'published'])],
            'archive' => ['%d annonce(s) archivée(s).', fn (Collection $items) => $this->updateEach($items, ['status' => 'archived'])],
            'draft' => ['%d annonce(s) repassée(s) en brouillon.', fn (Collection $items) => $this->updateEach($items, ['status' => 'draft'])],
            'delete' => ['%d annonce(s) supprimée(s).', fn (Collection $items) => $this->deleteEach($items)],
        ];
    }
}
