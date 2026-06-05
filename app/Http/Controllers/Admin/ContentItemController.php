<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderContentItemsRequest;
use App\Http\Requests\Admin\StoreContentItemRequest;
use App\Http\Requests\Admin\UpdateContentItemRequest;
use App\Models\ContentItem;
use App\Services\ContentSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;

class ContentItemController extends Controller
{
    public function store(StoreContentItemRequest $request, string $collectionKey, ContentSanitizer $sanitizer): RedirectResponse
    {
        $data = $this->sanitize($request->validated(), $sanitizer);

        $data['collection_key'] = $collectionKey;
        $data['position'] = $data['position'] ?? ((int) ContentItem::byCollection($collectionKey)->max('position') + 1);
        $data['is_active'] = $data['is_active'] ?? true;

        ContentItem::create($data);

        return back(303);
    }

    public function update(UpdateContentItemRequest $request, ContentItem $contentItem, ContentSanitizer $sanitizer): RedirectResponse
    {
        $contentItem->update($this->sanitize($request->validated(), $sanitizer));

        return back(303);
    }

    public function destroy(ContentItem $contentItem): RedirectResponse
    {
        $contentItem->delete();

        return back(303);
    }

    public function reorder(ReorderContentItemsRequest $request, string $collectionKey): RedirectResponse
    {
        foreach ($request->validated('items') as $item) {
            ContentItem::byCollection($collectionKey)
                ->whereKey($item['id'])
                ->update(['position' => $item['position']]);
        }

        return back(303);
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
