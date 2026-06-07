<?php

namespace App\Services;

use App\Models\ContentBlock;
use App\Models\ContentItem;
use App\Models\ContentRevision;
use Illuminate\Support\Arr;

class ContentRevisionRecorder
{
    public function snapshotBlock(ContentBlock $block): array
    {
        return Arr::only($block->fresh()?->toArray() ?? $block->toArray(), [
            'id',
            'key',
            'locale',
            'type',
            'value',
            'meta',
            'group',
            'position',
            'is_active',
            'created_at',
            'updated_at',
        ]);
    }

    public function snapshotItem(ContentItem $item): array
    {
        return Arr::only($item->fresh()?->toArray() ?? $item->toArray(), [
            'id',
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
            'created_at',
            'updated_at',
        ]);
    }

    public function recordBlock(ContentBlock $block, string $action, ?array $before, ?array $after): ContentRevision
    {
        return ContentRevision::create([
            'user_id' => auth()->id(),
            'content_type' => 'block',
            'content_id' => $block->id,
            'content_key' => $block->key,
            'action' => $action,
            'before' => $before,
            'after' => $after,
        ]);
    }

    public function recordItem(ContentItem $item, string $action, ?array $before, ?array $after): ContentRevision
    {
        return ContentRevision::create([
            'user_id' => auth()->id(),
            'content_type' => 'item',
            'content_id' => $item->id,
            'content_key' => $item->collection_key,
            'action' => $action,
            'before' => $before,
            'after' => $after,
        ]);
    }

    public function recordItemDeletion(ContentItem $item, array $before): ContentRevision
    {
        return ContentRevision::create([
            'user_id' => auth()->id(),
            'content_type' => 'item',
            'content_id' => $item->id,
            'content_key' => $item->collection_key,
            'action' => 'deleted',
            'before' => $before,
            'after' => null,
        ]);
    }

    public function recordCollectionReorder(string $collectionKey, array $before, array $after): ContentRevision
    {
        return ContentRevision::create([
            'user_id' => auth()->id(),
            'content_type' => 'collection',
            'content_id' => null,
            'content_key' => $collectionKey,
            'action' => 'reordered',
            'before' => $before,
            'after' => $after,
        ]);
    }
}
