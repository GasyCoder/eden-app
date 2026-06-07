<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentItem;
use App\Services\ContentRevisionRecorder;
use App\Services\ContentSanitizer;
use App\Support\ContentLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class VisitorPopupController extends Controller
{
    public function store(
        Request $request,
        ContentSanitizer $sanitizer,
        ContentRevisionRecorder $revisions,
    ): RedirectResponse {
        $data = $this->sanitize($this->validated($request), $sanitizer);
        $locale = ContentLocale::fromRequest($request);

        if ($data['is_active'] ?? true) {
            ContentItem::byCollection('visitor_popups')->locale($locale)->update(['is_active' => false]);
        }

        $item = ContentItem::create([
            ...$data,
            'collection_key' => 'visitor_popups',
            'locale' => $locale,
            'position' => (int) ContentItem::byCollection('visitor_popups')->locale($locale)->max('position') + 1,
            'is_active' => $data['is_active'] ?? true,
        ]);

        $revisions->recordItem($item, 'created', null, $revisions->snapshotItem($item));

        return back(303)->with('success', 'Annonce ajoutée.');
    }

    public function update(
        Request $request,
        ContentItem $contentItem,
        ContentSanitizer $sanitizer,
        ContentRevisionRecorder $revisions,
    ): RedirectResponse {
        $this->ensureVisitorPopup($contentItem, ContentLocale::fromRequest($request));

        $data = $this->sanitize($this->validated($request, partial: true), $sanitizer);
        $before = $revisions->snapshotItem($contentItem);

        if (($data['is_active'] ?? false) === true) {
            ContentItem::byCollection('visitor_popups')
                ->locale($contentItem->locale)
                ->whereKeyNot($contentItem->id)
                ->update(['is_active' => false]);
        }

        $contentItem->update($data);

        $revisions->recordItem($contentItem, 'updated', $before, $revisions->snapshotItem($contentItem));

        return back(303)->with('success', 'Annonce enregistrée.');
    }

    public function activate(ContentItem $contentItem, ContentRevisionRecorder $revisions): RedirectResponse
    {
        $this->ensureVisitorPopup($contentItem, ContentLocale::fromRequest());

        $before = $revisions->snapshotItem($contentItem);

        ContentItem::byCollection('visitor_popups')
            ->locale($contentItem->locale)
            ->whereKeyNot($contentItem->id)
            ->update(['is_active' => false]);

        $meta = $contentItem->meta ?? [];
        unset($meta['archived_at']);

        $contentItem->update([
            'is_active' => true,
            'meta' => $meta,
        ]);

        $revisions->recordItem($contentItem, 'updated', $before, $revisions->snapshotItem($contentItem));

        return back(303)->with('success', 'Annonce activée.');
    }

    public function archive(ContentItem $contentItem, ContentRevisionRecorder $revisions): RedirectResponse
    {
        $this->ensureVisitorPopup($contentItem, ContentLocale::fromRequest());

        $before = $revisions->snapshotItem($contentItem);
        $meta = $contentItem->meta ?? [];

        $contentItem->update([
            'is_active' => false,
            'meta' => [
                ...$meta,
                'archived_at' => now()->toISOString(),
            ],
        ]);

        $revisions->recordItem($contentItem, 'updated', $before, $revisions->snapshotItem($contentItem));

        return back(303)->with('success', 'Annonce archivée.');
    }

    public function destroy(ContentItem $contentItem, ContentRevisionRecorder $revisions): RedirectResponse
    {
        $this->ensureVisitorPopup($contentItem, ContentLocale::fromRequest());

        $before = $revisions->snapshotItem($contentItem);

        $contentItem->delete();

        $revisions->recordItemDeletion($contentItem, $before);

        return back(303)->with('success', 'Annonce supprimée.');
    }

    private function ensureVisitorPopup(ContentItem $contentItem, string $locale): void
    {
        abort_unless($contentItem->collection_key === 'visitor_popups' && $contentItem->locale === $locale, 404);
    }

    private function validated(Request $request, bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'nullable';

        return $request->validate([
            'title' => [$required, 'nullable', 'string', 'max:255'],
            'description' => [$required, 'nullable', 'string'],
            'image' => [$required, 'nullable', 'string', 'max:2048'],
            'link_url' => [$required, 'nullable', 'string', 'max:2048'],
            'link_label' => [$required, 'nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'meta' => ['sometimes', 'nullable', 'array'],
            'meta.delay_seconds' => ['sometimes', 'nullable', 'integer', Rule::in([1, 2, 3, 5, 10, 15, 30])],
            'meta.object_position_x' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
            'meta.object_position_y' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
            'meta.object_zoom' => ['sometimes', 'nullable', 'numeric', 'min:1', 'max:2'],
        ]);
    }

    private function sanitize(array $data, ContentSanitizer $sanitizer): array
    {
        foreach (['title', 'link_label'] as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = $sanitizer->clean($data[$field]);
            }
        }

        if (array_key_exists('description', $data)) {
            $data['description'] = $sanitizer->clean($data['description'], 'textarea');
        }

        foreach (['image', 'link_url'] as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = $sanitizer->clean($data[$field], 'link');
            }
        }

        if (array_key_exists('meta', $data)) {
            $data['meta'] = $sanitizer->cleanMixed($data['meta']);
        }

        return Arr::only($data, [
            'title',
            'description',
            'image',
            'link_url',
            'link_label',
            'meta',
            'is_active',
        ]);
    }
}
