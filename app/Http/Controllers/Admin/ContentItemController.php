<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderContentItemsRequest;
use App\Http\Requests\Admin\StoreContentItemRequest;
use App\Http\Requests\Admin\UpdateContentItemRequest;
use App\Models\ContentItem;
use App\Services\ContentRevisionRecorder;
use App\Services\ContentSanitizer;
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

        $data['collection_key'] = $collectionKey;
        $data['position'] = $data['position'] ?? ((int) ContentItem::byCollection($collectionKey)->max('position') + 1);
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
        $before = $revisions->snapshotItem($contentItem);

        $contentItem->update($this->sanitize($request->validated(), $sanitizer));

        $revisions->recordItem($contentItem, 'updated', $before, $revisions->snapshotItem($contentItem));

        return back(303)->with('success', 'Élément enregistré.');
    }

    public function destroy(ContentItem $contentItem, ContentRevisionRecorder $revisions): RedirectResponse
    {
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
        $before = ContentItem::byCollection($collectionKey)
            ->ordered()
            ->get(['id', 'title', 'position'])
            ->toArray();

        foreach ($request->validated('items') as $item) {
            ContentItem::byCollection($collectionKey)
                ->whereKey($item['id'])
                ->update(['position' => $item['position']]);
        }

        $after = ContentItem::byCollection($collectionKey)
            ->ordered()
            ->get(['id', 'title', 'position'])
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
