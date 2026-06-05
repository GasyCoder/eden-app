<?php

namespace App\Services;

use App\Models\ContentBlock;
use App\Models\ContentItem;
use Illuminate\Support\Arr;

class ContentRepository
{
    public function __construct(private readonly ImageVariantService $images)
    {
    }

    public function blocks(): array
    {
        $blocks = [];

        ContentBlock::query()
            ->active()
            ->ordered()
            ->get(['key', 'value'])
            ->each(function (ContentBlock $block) use (&$blocks): void {
                Arr::set($blocks, $block->key, $block->value);
            });

        return $blocks;
    }

    public function collections(): array
    {
        return ContentItem::query()
            ->active()
            ->ordered()
            ->get()
            ->groupBy('collection_key')
            ->map(fn ($items) => $items->map(fn (ContentItem $item) => [
                'id' => $item->id,
                'title' => $item->title,
                'subtitle' => $item->subtitle,
                'description' => $item->description,
                'image' => $item->image,
                'image_sources' => $this->images->forUrl($item->image),
                'link_url' => $item->link_url,
                'link_label' => $item->link_label,
                'meta' => $item->meta ?? [],
                'position' => $item->position,
            ])->values()->all())
            ->all();
    }
}
