<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderContentItemsRequest;
use App\Http\Requests\Admin\StoreContentItemRequest;
use App\Http\Requests\Admin\UpdateContentItemRequest;
use App\Models\ContentItem;
use App\Services\ContentRevisionRecorder;
use App\Services\ContentSanitizer;
use App\Support\ContentLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;

class ContentItemController extends Controller
{
    public function store(
        StoreContentItemRequest $request,
        string $collectionKey,
        ContentSanitizer $sanitizer,
        ContentRevisionRecorder $revisions,
    ): RedirectResponse {
        $data = $this->sanitize($request->validated(), $sanitizer);
        $locale = ContentLocale::fromRequest($request);

        $data['collection_key'] = $collectionKey;
        $data['locale'] = $locale;
        $data['position'] = $data['position'] ?? ((int) ContentItem::byCollection($collectionKey)->locale($locale)->max('position') + 1);
        $data['is_active'] = $data['is_active'] ?? true;

        $item = ContentItem::create($data);

        $revisions->recordItem($item, 'created', null, $revisions->snapshotItem($item));

        return back(303)->with('success', 'Élément ajouté.');
    }

    public function update(
        UpdateContentItemRequest $request,
        ContentItem $contentItem,
        ContentSanitizer $sanitizer,
        ContentRevisionRecorder $revisions,
    ): RedirectResponse {
        abort_unless($contentItem->locale === ContentLocale::fromRequest($request), 404);

        $before = $revisions->snapshotItem($contentItem);

        $contentItem->update($this->sanitize($request->validated(), $sanitizer));

        $revisions->recordItem($contentItem, 'updated', $before, $revisions->snapshotItem($contentItem));

        return back(303)->with('success', 'Élément enregistré.');
    }

    public function destroy(ContentItem $contentItem, ContentRevisionRecorder $revisions): RedirectResponse
    {
        abort_unless($contentItem->locale === ContentLocale::fromRequest(), 404);

        $before = $revisions->snapshotItem($contentItem);

        $contentItem->delete();

        $revisions->recordItemDeletion($contentItem, $before);

        return back(303)->with('success', 'Élément supprimé.');
    }

    public function reorder(
        ReorderContentItemsRequest $request,
        string $collectionKey,
        ContentRevisionRecorder $revisions,
    ): RedirectResponse {
        $locale = ContentLocale::fromRequest($request);
        $before = ContentItem::byCollection($collectionKey)
            ->locale($locale)
            ->ordered()
            ->get(['id', 'title', 'position', 'locale'])
            ->toArray();

        foreach ($request->validated('items') as $item) {
            ContentItem::byCollection($collectionKey)
                ->locale($locale)
                ->whereKey($item['id'])
                ->update(['position' => $item['position']]);
        }

        $after = ContentItem::byCollection($collectionKey)
            ->locale($locale)
            ->ordered()
            ->get(['id', 'title', 'position', 'locale'])
            ->toArray();

        $revisions->recordCollectionReorder($collectionKey, $before, $after);

        return back(303)->with('success', 'Ordre enregistré.');
    }

    private function sanitize(array $data, ContentSanitizer $sanitizer): array
    {
        foreach (['title', 'subtitle', 'link_label'] as $field) {
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
            'collection_key',
            'locale',
            'title',
            'subtitle',
            'description',
            'image',
            'link_url',
            'link_label',
            'meta',
            'position',
            'is_active',
        ]);
    }

}
