<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesBulkActions;
use App\Http\Controllers\Admin\Concerns\HandlesEditorialInput;
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\ContentBlock;
use App\Models\Event;
use App\Models\HeroSlide;
use App\Models\JournalIssue;
use App\Models\Post;
use App\Models\Project;
use App\Services\ContentRevisionRecorder;
use App\Services\ContentSanitizer;
use App\Services\ImageVariantService;
use App\Support\ContentLocale;
use App\Support\EditorialDate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Héro de l'accueil : textes, boutons et diapositives reliées aux contenus du site.
 */
class HeroController extends Controller
{
    use HandlesBulkActions, HandlesEditorialInput;

    /** Textes du héro (blocs éditables, aussi modifiables directement sur l'accueil). */
    private const TEXT_KEYS = ['title', 'title_highlight', 'subtitle', 'button_primary_label', 'button_secondary_label'];

    private const URL_KEYS = ['button_primary_url', 'button_secondary_url'];

    public function index(): Response
    {
        $blocks = ContentBlock::query()->where('key', 'like', 'hero.%')->get()->groupBy('locale');
        $value = fn (string $key, string $locale) => (string) ($blocks->get($locale)?->firstWhere('key', "hero.{$key}")?->value ?? '');

        return Inertia::render('Admin/Hero/Index', [
            'texts' => collect(self::TEXT_KEYS)->mapWithKeys(fn ($key) => [$key => collect(ContentLocale::SUPPORTED)->mapWithKeys(fn ($locale) => [$locale => $value($key, $locale)])->all()])->all(),
            'urls' => collect(self::URL_KEYS)->mapWithKeys(fn ($key) => [$key => $value($key, ContentLocale::DEFAULT)])->all(),
            'slides' => HeroSlide::query()->with('linkable')->ordered()->get()->map->toAdminRow()->values()->all(),
        ]);
    }

    public function updateTexts(Request $request, ContentSanitizer $sanitizer, ContentRevisionRecorder $revisions): RedirectResponse
    {
        $rules = [];

        foreach (self::TEXT_KEYS as $key) {
            $rules += $this->translatableRules($key, required: $key === 'title', max: $key === 'subtitle' ? 600 : 190);
        }

        foreach (self::URL_KEYS as $key) {
            $rules[$key] = $this->linkRule();
        }

        $data = $request->validate($rules, ['title.fr.required' => 'Le titre en français est obligatoire.', '*.regex' => 'Lien attendu : /page, #section ou https://…']);

        foreach (self::TEXT_KEYS as $key) {
            foreach (ContentLocale::SUPPORTED as $locale) {
                $type = $key === 'subtitle' ? 'textarea' : 'text';
                $this->setBlock("hero.{$key}", $locale, $sanitizer->clean($data[$key][$locale] ?? null, $type), $type, $revisions);
            }
        }

        foreach (self::URL_KEYS as $key) {
            $this->setBlock("hero.{$key}", ContentLocale::DEFAULT, $sanitizer->clean($data[$key] ?? null, 'link'), 'link', $revisions);
        }

        return back(303)->with('success', 'Textes du héro enregistrés.');
    }

    public function create(): Response
    {
        return $this->form(new HeroSlide(['is_active' => true, 'link_type' => 'none', 'focal_point' => 'center']));
    }

    public function store(Request $request): RedirectResponse
    {
        HeroSlide::create([...$this->payload($request), 'position' => (int) HeroSlide::query()->max('position') + 1]);

        return to_route('admin.hero.index')->with('success', 'Diapositive ajoutée.');
    }

    public function edit(HeroSlide $slide): Response
    {
        return $this->form($slide);
    }

    public function update(Request $request, HeroSlide $slide): RedirectResponse
    {
        $slide->update($this->payload($request));

        return to_route('admin.hero.index')->with('success', 'Diapositive enregistrée.');
    }

    public function destroy(HeroSlide $slide): RedirectResponse
    {
        $slide->delete();

        return to_route('admin.hero.index')->with('success', 'Diapositive supprimée.');
    }

    public function reorder(Request $request): RedirectResponse
    {
        $ids = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']])['ids'];

        foreach (array_values($ids) as $position => $id) {
            HeroSlide::query()->whereKey($id)->update(['position' => $position]);
        }

        return back(303)->with('success', 'Ordre du diaporama enregistré.');
    }

    protected function bulkModel(): string
    {
        return HeroSlide::class;
    }

    protected function bulkActions(): array
    {
        return [
            'show' => ['%d diapositive(s) affichée(s).', fn (Collection $slides) => $this->updateEach($slides, ['is_active' => true])],
            'hide' => ['%d diapositive(s) masquée(s).', fn (Collection $slides) => $this->updateEach($slides, ['is_active' => false])],
            'delete' => ['%d diapositive(s) supprimée(s).', fn (Collection $slides) => $this->deleteEach($slides)],
        ];
    }

    private function form(HeroSlide $slide): Response
    {
        return Inertia::render('Admin/Hero/Form', [
            'slide' => $slide->toForm(),
            'linkables' => $this->linkables(),
        ]);
    }

    /** Contenus pouvant être reliés à une diapositive (les plus récents d'abord). */
    private function linkables(): array
    {
        $option = fn (int $id, ?string $label, ?string $meta = null, ?string $image = null, bool $public = true) => compact('id', 'label', 'meta', 'image', 'public');
        $date = fn ($value) => $value?->timezone(EditorialDate::timezone())->format('d/m/Y');

        return [
            'post' => Post::query()->latest('published_at')->limit(100)->get()
                ->map(fn (Post $post) => $option($post->id, $post->translate('title', 'fr'), $date($post->published_at), $post->cover_image, $post->isPublic()))->all(),
            'event' => Event::query()->orderByDesc('starts_at')->limit(100)->get()
                ->map(fn (Event $event) => $option($event->id, $event->translate('title', 'fr'), $date($event->starts_at), $event->cover_image, $event->isPublic()))->all(),
            'announcement' => Announcement::query()->latest('updated_at')->limit(100)->get()
                ->map(fn (Announcement $item) => $option($item->id, $item->translate('title', 'fr'), $item->cta_url ? null : 'Sans lien : choisissez une annonce avec un bouton', $item->image, $item->status === 'published' && (bool) $item->cta_url))->all(),
            'project' => Project::query()->ordered()->get()
                ->map(fn (Project $project) => $option($project->id, $project->translate('title', 'fr'), $project->period(), $project->cover_image, $project->isPublic()))->all(),
            'journal_issue' => JournalIssue::query()->with('journal')->latestFirst()->limit(100)->get()
                ->map(fn (JournalIssue $issue) => $option($issue->id, trim(($issue->journal?->translate('title', 'fr') ?? '').' '.$issue->label()), $issue->translate('period', 'fr'), $issue->cover_image, $issue->isPublic()))->all(),
        ];
    }

    private function payload(Request $request): array
    {
        $data = $request->validate([
            'image' => ['nullable', 'string', 'max:2048', 'required_if:link_type,none,url'],
            'focal_point' => ['required', Rule::in(HeroSlide::FOCAL_POINTS)],
            ...$this->translatableRules('title', max: 190),
            ...$this->translatableRules('subtitle', max: 190),
            ...$this->translatableRules('cta_label', max: 60),
            'link_type' => ['required', Rule::in(array_keys(HeroSlide::LINK_TYPES))],
            'linkable_id' => ['nullable', 'integer', Rule::requiredIf(fn () => HeroSlide::LINK_TYPES[$request->input('link_type')] ?? null)],
            'url' => ['nullable', 'required_if:link_type,url', ...array_slice($this->linkRule(), 1)],
            'is_active' => ['boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ], [
            'image.required_if' => 'Ajoutez une image (ou reliez un contenu qui en possède une).',
            'linkable_id.required' => 'Choisissez le contenu à relier.',
            'url.required_if' => 'Indiquez l’adresse du lien.',
            'url.regex' => 'Lien attendu : /page, #section ou https://…',
            'ends_at.after_or_equal' => 'La fin doit suivre le début.',
        ]);

        $class = HeroSlide::LINK_TYPES[$data['link_type']];

        if ($class && ! $class::query()->whereKey($data['linkable_id'])->exists()) {
            throw ValidationException::withMessages(['linkable_id' => 'Ce contenu n’existe plus.']);
        }

        if (filled($data['image'] ?? null)) {
            app(ImageVariantService::class)->forUrl($data['image'], ImageVariantService::HERO_WIDTHS, ImageVariantService::HERO_SIZES);
        }

        return [
            'image' => $this->plain($data['image'] ?? null, 'image'),
            'focal_point' => $data['focal_point'],
            'title' => $this->translated($data, 'title'),
            'subtitle' => $this->translated($data, 'subtitle'),
            'cta_label' => $this->translated($data, 'cta_label'),
            'link_type' => $data['link_type'],
            'linkable_type' => $class,
            'linkable_id' => $class ? $data['linkable_id'] : null,
            'url' => $data['link_type'] === 'url' ? $this->plain($data['url'] ?? null, 'link') : null,
            'is_active' => (bool) ($data['is_active'] ?? true),
            'starts_at' => EditorialDate::fromInput($data['starts_at'] ?? null),
            'ends_at' => EditorialDate::fromInput($data['ends_at'] ?? null),
        ];
    }

    private function setBlock(string $key, string $locale, ?string $value, string $type, ContentRevisionRecorder $revisions): void
    {
        $value = $value === '' ? null : $value;
        $block = ContentBlock::firstOrNew(['key' => $key, 'locale' => $locale]);

        if ((string) $block->value === (string) $value || (! $block->exists && $value === null)) {
            return;
        }

        $before = $block->exists ? $revisions->snapshotBlock($block) : null;
        $existed = $block->exists;
        $block->fill(['type' => $block->type ?? $type, 'group' => $block->group ?? 'hero', 'value' => $value, 'is_active' => true])->save();
        $revisions->recordBlock($block, $existed ? 'updated' : 'created', $before, $revisions->snapshotBlock($block));
    }
}
