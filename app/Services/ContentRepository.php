<?php

namespace App\Services;

use App\Models\ContentBlock;
use App\Models\ContentItem;
use App\Support\ContentLocale;
use Illuminate\Support\Arr;

class ContentRepository
{
    public function __construct(private readonly ImageVariantService $images) {}

    public function blocks(string $locale = ContentLocale::DEFAULT): array
    {
        $blocks = [];
        $locales = $this->fallbackLocales($locale);

        foreach ($locales as $activeLocale) {
            ContentBlock::query()
                ->active()
                ->locale($activeLocale)
                ->ordered()
                ->get(['key', 'value'])
                ->each(function (ContentBlock $block) use (&$blocks): void {
                    if ($block->value !== null && $block->value !== '') {
                        Arr::set($blocks, $block->key, $block->value);
                    }
                });
        }

        return $blocks;
    }

    public function blockMeta(string $locale = ContentLocale::DEFAULT): array
    {
        $meta = [];
        $locales = $this->fallbackLocales($locale);

        foreach ($locales as $activeLocale) {
            ContentBlock::query()
                ->active()
                ->locale($activeLocale)
                ->whereNotNull('meta')
                ->get(['key', 'meta'])
                ->each(function (ContentBlock $block) use (&$meta): void {
                    if ($block->meta !== []) {
                        Arr::set($meta, $block->key, $block->meta ?? []);
                    }
                });
        }

        return $meta;
    }

    public function collections(string $locale = ContentLocale::DEFAULT): array
    {
        $fallback = ContentItem::query()
            ->active()
            ->locale(ContentLocale::DEFAULT)
            ->ordered()
            ->get()
            ->groupBy('collection_key');

        if (ContentLocale::normalize($locale) === ContentLocale::DEFAULT) {
            return $fallback
                ->map(fn ($items) => $items->map(fn (ContentItem $item) => $this->serializeItem($item))->values()->all())
                ->all();
        }

        $localized = ContentItem::query()
            ->active()
            ->locale(ContentLocale::normalize($locale))
            ->ordered()
            ->get()
            ->groupBy('collection_key');

        return $fallback
            ->keys()
            ->merge($localized->keys())
            ->unique()
            ->mapWithKeys(function (string $collectionKey) use ($fallback, $localized): array {
                $localizedItems = $localized->get($collectionKey);

                if ($localizedItems && $localizedItems->isNotEmpty()) {
                    return [$collectionKey => $localizedItems->map(fn (ContentItem $item) => $this->serializeItem($item))->values()->all()];
                }

                return [$collectionKey => ($fallback->get($collectionKey) ?? collect())
                    ->map(fn (ContentItem $item) => $this->serializeItem($item, editable: false))
                    ->values()
                    ->all()];
            })
            ->all();
    }

    public function visitorPopup(array $fallback = [], string $locale = ContentLocale::DEFAULT): array
    {
        $locale = ContentLocale::normalize($locale);
        $query = ContentItem::query()->byCollection('visitor_popups')->locale($locale);
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

        if ($locale !== ContentLocale::DEFAULT) {
            return $this->visitorPopup($fallback, ContentLocale::DEFAULT);
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

    public function visitorPopups(string $locale = ContentLocale::DEFAULT): array
    {
        return ContentItem::query()
            ->byCollection('visitor_popups')
            ->locale(ContentLocale::normalize($locale))
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

    /**
     * @return array<int, string>
     */
    private function fallbackLocales(string $locale): array
    {
        $locale = ContentLocale::normalize($locale);

        return $locale === ContentLocale::DEFAULT
            ? [ContentLocale::DEFAULT]
            : [ContentLocale::DEFAULT, $locale];
    }

    private function serializeItem(ContentItem $item, bool $editable = true): array
    {
        return [
            'id' => $editable ? $item->id : null,
            'title' => $item->title,
            'subtitle' => $item->subtitle,
            'description' => $item->description,
            'image' => $item->image,
            'image_sources' => $this->images->forUrl($item->image),
            'link_url' => $item->link_url,
            'link_label' => $item->link_label,
            'meta' => $item->meta ?? [],
            'position' => $item->position,
            'locale' => $item->locale,
            'is_fallback' => ! $editable,
        ];
    }
}
