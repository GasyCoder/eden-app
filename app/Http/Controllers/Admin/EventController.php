<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesBulkActions;
use App\Http\Controllers\Admin\Concerns\HandlesEditorialInput;
use App\Http\Controllers\Admin\Concerns\NotifiesSubscribers;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Support\EditorialDate;
use App\Support\Paginated;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    use HandlesBulkActions, HandlesEditorialInput, NotifiesSubscribers;

    public function index(Request $request): Response
    {
        $search = trim($request->string('q')->toString());
        $status = $request->string('status')->toString();
        $period = $request->string('period')->toString();

        $events = Event::query()
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('title->fr', 'like', "%{$search}%")
                ->orWhere('title->en', 'like', "%{$search}%")
                ->orWhere('location', 'like', "%{$search}%")))
            ->when(in_array($status, Event::STATUSES, true), fn (Builder $query) => $query->where('status', $status))
            ->when($period === 'upcoming', fn (Builder $query) => $query->upcoming())
            ->when($period === 'past', fn (Builder $query) => $query->past())
            ->when(! in_array($period, ['upcoming', 'past'], true), fn (Builder $query) => $query->orderByDesc('starts_at'))
            ->paginate(15);

        return Inertia::render('Admin/Events/Index', [
            'events' => Paginated::from($events, fn (Event $event) => $event->toAdminRow()),
            'filters' => ['q' => $search, 'status' => $status, 'period' => $period],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Events/Form', [
            'event' => (new Event(['status' => 'draft', 'format' => 'in_person', 'is_featured' => false, 'is_all_day' => false]))->toForm(),
            'newsletter' => $this->newsletterState(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $event = Event::create($this->payload($request));

        return to_route('admin.events.edit', $event)->with('success', 'Événement enregistré.'.$this->notifySubscribers($request, $event));
    }

    public function edit(Event $event): Response
    {
        return Inertia::render('Admin/Events/Form', [
            'event' => $event->toForm(),
            'publicUrl' => route('events.show', $event->slug),
            'newsletter' => $this->newsletterState($event),
        ]);
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $event->update($this->payload($request, $event));

        return to_route('admin.events.edit', $event, 303)->with('success', 'Événement enregistré.'.$this->notifySubscribers($request, $event));
    }

    public function destroy(Event $event): RedirectResponse
    {
        $event->delete();

        return to_route('admin.events.index')->with('success', 'Événement supprimé.');
    }

    private function payload(Request $request, ?Event $event = null): array
    {
        $validator = Validator::make($request->all(), [
            ...$this->translatableRules('title', required: true),
            ...$this->translatableRules('summary', max: 600),
            ...$this->translatableRules('body', html: true),
            'slug' => ['nullable', 'string', 'max:190', 'alpha_dash'],
            'cover_image' => ['nullable', 'string', 'max:2048'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_all_day' => ['boolean'],
            'location' => ['nullable', 'string', 'max:255'],
            'format' => ['required', Rule::in(Event::FORMATS)],
            'registration_url' => ['nullable', 'url:http,https', 'max:2048'],
            'organizer' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(Event::STATUSES)],
            'is_featured' => ['boolean'],
        ], [
            'title.fr.required' => 'Le titre en français est obligatoire.',
            'starts_at.required' => 'Indiquez la date de début.',
            'ends_at.after_or_equal' => 'La fin doit être postérieure au début.',
            'registration_url.url' => 'Indiquez une adresse complète commençant par https://.',
        ]);

        if ($validator->fails()) {
            throw (new ValidationException($validator))->redirectTo(
                $event ? route('admin.events.edit', $event) : route('admin.events.create'),
            );
        }

        $data = $validator->validated();

        return [
            'title' => $this->translated($data, 'title'),
            'summary' => $this->translated($data, 'summary', 'textarea'),
            'body' => $this->translated($data, 'body', 'html'),
            'slug' => $this->uniqueSlug(Event::class, $data['slug'] ?? null, $data['title']['fr'] ?? null, $event),
            'cover_image' => $this->plain($data['cover_image'] ?? null, 'image'),
            'starts_at' => EditorialDate::fromInput($data['starts_at']),
            'ends_at' => EditorialDate::fromInput($data['ends_at'] ?? null),
            'is_all_day' => (bool) ($data['is_all_day'] ?? false),
            'location' => $this->plain($data['location'] ?? null),
            'format' => $data['format'],
            'registration_url' => $this->plain($data['registration_url'] ?? null, 'link'),
            'organizer' => $this->plain($data['organizer'] ?? null),
            'status' => $data['status'],
            'is_featured' => (bool) ($data['is_featured'] ?? false),
        ];
    }

    protected function bulkModel(): string
    {
        return Event::class;
    }

    protected function bulkActions(): array
    {
        return [
            'publish' => ['%d événement(s) publié(s).', fn (Collection $events) => $this->updateEach($events, ['status' => 'published'])],
            'draft' => ['%d événement(s) repassé(s) en brouillon.', fn (Collection $events) => $this->updateEach($events, ['status' => 'draft'])],
            'complete' => ['%d événement(s) marqué(s) comme terminés.', fn (Collection $events) => $this->updateEach($events, ['status' => 'completed'])],
            'archive' => ['%d événement(s) archivé(s).', fn (Collection $events) => $this->updateEach($events, ['status' => 'archived'])],
            'cancel' => ['%d événement(s) annulé(s).', fn (Collection $events) => $this->updateEach($events, ['status' => 'cancelled'])],
            'delete' => ['%d événement(s) supprimé(s).', fn (Collection $events) => $this->deleteEach($events)],
        ];
    }
}
