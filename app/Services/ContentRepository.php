<?php

namespace App\Services;

use App\Models\ContentBlock;
use App\Models\ContentItem;
use Illuminate\Support\Arr;

class ContentRepository
{
    public function __construct(private readonly ImageVariantService $images) {}

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

    public function blockMeta(): array
    {
        $meta = [];

        ContentBlock::query()
            ->active()
            ->whereNotNull('meta')
            ->get(['key', 'meta'])
            ->each(function (ContentBlock $block) use (&$meta): void {
                if ($block->meta !== []) {
                    Arr::set($meta, $block->key, $block->meta ?? []);
                }
            });

        return $meta;
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

    public function visitorPopup(array $fallback = []): array
    {
        $query = ContentItem::query()->byCollection('visitor_popups');
        $item = (clone $query)->active()->latest('updated_at')->first();

        if ($item) {
            return $this->serializeVisitorPopup($item);
        }

        if ((clone $query)->exists()) {
            $latest = (clone $query)->latest('updated_at')->first();
            $popup = $latest ? $this->serializeVisitorPopup($latest) : [];

            return [
                ...$popup,
                'enabled' => '0',
                'is_active' => false,
            ];
        }

        return [
            'id' => null,
            'enabled' => '0',
            'delay_seconds' => $fallback['delay_seconds'] ?? '3',
            'title' => $fallback['title'] ?? 'Annonce importante',
            'message' => $fallback['message'] ?? "Une nouvelle information est disponible pour les visiteurs de l'École Doctorale EDEN.",
            'image' => $fallback['image'] ?? null,
            'button_label' => $fallback['button_label'] ?? 'En savoir plus',
            'button_url' => $fallback['button_url'] ?? '#',
            'meta' => [],
            'is_active' => false,
            'created_at' => null,
            'updated_at' => null,
        ];
    }

    public function visitorPopups(): array
    {
        return ContentItem::query()
            ->byCollection('visitor_popups')
            ->latest('updated_at')
            ->get()
            ->map(fn (ContentItem $item) => $this->serializeVisitorPopup($item))
            ->values()
            ->all();
    }

    private function serializeVisitorPopup(ContentItem $item): array
    {
        $meta = $item->meta ?? [];

        return [
            'id' => $item->id,
            'enabled' => $item->is_active ? '1' : '0',
            'delay_seconds' => (string) ($meta['delay_seconds'] ?? 3),
            'title' => $item->title,
            'message' => $item->description,
            'image' => $item->image,
            'image_sources' => $this->images->forUrl($item->image),
            'button_label' => $item->link_label,
            'button_url' => $item->link_url,
            'meta' => $meta,
            'is_active' => $item->is_active,
            'is_archived' => !$item->is_active && isset($meta['archived_at']),
            'created_at' => $item->created_at?->format('d/m/Y'),
            'updated_at' => $item->updated_at?->format('d/m/Y H:i'),
        ];
    }
}
