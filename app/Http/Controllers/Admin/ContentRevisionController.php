<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentBlock;
use App\Models\ContentItem;
use App\Models\ContentRevision;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;

class ContentRevisionController extends Controller
{
    public function index(): JsonResponse
    {
        $revisions = ContentRevision::with('user')
            ->latest()
            ->limit(40)
            ->get()
            ->map(fn (ContentRevision $revision) => [
                'id' => $revision->id,
                'action' => $revision->action,
                'content_type' => $revision->content_type,
                'content_key' => $revision->content_key,
                'label' => $this->label($revision),
                'detail' => $this->detail($revision),
                'changes' => $this->changes($revision),
                'user' => $revision->user?->name ?? 'Administrateur',
                'created_at' => $revision->created_at?->diffForHumans(),
                'can_restore' => $this->canRestore($revision),
            ]);

        return response()->json(['revisions' => $revisions]);
    }

    public function restore(ContentRevision $contentRevision): RedirectResponse
    {
        if (! $this->canRestore($contentRevision)) {
            return back(303)->with('error', 'Cette version ne peut pas être restaurée.');
        }

        match ($contentRevision->content_type) {
            'block' => $this->restoreBlock($contentRevision),
            'item' => $this->restoreItem($contentRevision),
            'collection' => $this->restoreCollection($contentRevision),
            default => null,
        };

        ContentRevision::create([
            'user_id' => auth()->id(),
            'content_type' => 'revision',
            'content_id' => $contentRevision->id,
            'content_key' => $contentRevision->content_key,
            'action' => 'restored',
            'before' => null,
            'after' => [
                'restored_revision_id' => $contentRevision->id,
                'restored_action' => $contentRevision->action,
            ],
        ]);

        return back(303)->with('success', 'Version restaurée.');
    }

    private function restoreBlock(ContentRevision $revision): void
    {
        if ($revision->action === 'created' && $revision->before === null) {
            $key = $revision->after['key'] ?? $revision->content_key;
            $locale = $revision->after['locale'] ?? 'fr';
            ContentBlock::where('key', $key)->where('locale', $locale)->delete();

            return;
        }

        $snapshot = $revision->before;

        ContentBlock::updateOrCreate(
            [
                'key' => $snapshot['key'],
                'locale' => $snapshot['locale'] ?? 'fr',
            ],
            Arr::only($snapshot, [
                'type',
                'value',
                'meta',
                'group',
                'position',
                'is_active',
            ]),
        );
    }

    private function restoreItem(ContentRevision $revision): void
    {
        if ($revision->action === 'created' && $revision->before === null) {
            ContentItem::whereKey($revision->after['id'] ?? $revision->content_id)->delete();

            return;
        }

        $snapshot = $revision->before;

        $item = ContentItem::withTrashed()->find($snapshot['id']) ?? new ContentItem;
        if ($item->exists && $item->trashed()) {
            $item->restore();
        }
        $item->forceFill(Arr::only($snapshot, [
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
        ]));
        $item->save();
    }

    private function restoreCollection(ContentRevision $revision): void
    {
        foreach ($revision->before ?? [] as $item) {
            ContentItem::byCollection($revision->content_key)
                ->where('locale', $item['locale'] ?? 'fr')
                ->whereKey($item['id'])
                ->update(['position' => $item['position']]);
        }
    }

    private function label(ContentRevision $revision): string
    {
        return match ($revision->content_type) {
            'block' => 'Bloc '.$revision->content_key,
            'item' => 'Carte '.$revision->content_key,
            'collection' => 'Ordre '.$revision->content_key,
            'revision' => 'Restauration',
            default => $revision->content_key ?? 'Contenu',
        };
    }

    private function detail(ContentRevision $revision): string
    {
        return match ($revision->action) {
            'created' => 'Création',
            'updated' => 'Modification',
            'deleted' => 'Suppression',
            'reordered' => 'Réordonnancement',
            'restored' => 'Restauration effectuée',
            default => ucfirst($revision->action),
        };
    }

    private function changes(ContentRevision $revision): array
    {
        if ($revision->content_type === 'collection') {
            return [[
                'field' => 'Ordre',
                'before' => $this->formatCollectionOrder($revision->before ?? []),
                'after' => $this->formatCollectionOrder($revision->after ?? []),
            ]];
        }

        if ($revision->content_type === 'revision') {
            return [[
                'field' => 'Action',
                'before' => 'Version actuelle',
                'after' => 'Version restaurée',
            ]];
        }

        $before = $revision->before ?? [];
        $after = $revision->after ?? [];
        $fields = $this->trackedFields($revision);

        if ($revision->action === 'created') {
            return collect($fields)
                ->filter(fn (string $field) => array_key_exists($field, $after) && filled($after[$field]))
                ->map(fn (string $field) => [
                    'field' => $this->fieldLabel($field),
                    'before' => 'Absent',
                    'after' => $this->formatValue($after[$field]),
                ])
                ->values()
                ->all();
        }

        if ($revision->action === 'deleted') {
            return collect($fields)
                ->filter(fn (string $field) => array_key_exists($field, $before) && filled($before[$field]))
                ->map(fn (string $field) => [
                    'field' => $this->fieldLabel($field),
                    'before' => $this->formatValue($before[$field]),
                    'after' => 'Supprimé',
                ])
                ->values()
                ->all();
        }

        return collect($fields)
            ->filter(fn (string $field) => ($before[$field] ?? null) !== ($after[$field] ?? null))
            ->map(fn (string $field) => [
                'field' => $this->fieldLabel($field),
                'before' => $this->formatValue($before[$field] ?? null),
                'after' => $this->formatValue($after[$field] ?? null),
            ])
            ->values()
            ->all();
    }

    private function trackedFields(ContentRevision $revision): array
    {
        if ($revision->content_type === 'block') {
            return ['value', 'type', 'meta', 'group', 'position', 'is_active'];
        }

        return [
            'title',
            'subtitle',
            'description',
            'image',
            'link_url',
            'link_label',
            'meta',
            'position',
            'is_active',
        ];
    }

    private function fieldLabel(string $field): string
    {
        return match ($field) {
            'value' => 'Valeur',
            'type' => 'Type',
            'meta' => 'Métadonnées',
            'group' => 'Groupe',
            'position' => 'Position',
            'is_active' => 'Actif',
            'title' => 'Titre',
            'subtitle' => 'Sous-titre',
            'description' => 'Description',
            'image' => 'Image',
            'link_url' => 'Lien',
            'link_label' => 'Libellé du lien',
            default => ucfirst(str_replace('_', ' ', $field)),
        };
    }

    private function formatCollectionOrder(array $items): string
    {
        if ($items === []) {
            return 'Aucun élément';
        }

        return collect($items)
            ->sortBy('position')
            ->map(fn (array $item) => ($item['title'] ?? 'Élément #'.($item['id'] ?? '?')).' ['.($item['position'] ?? 0).']')
            ->implode(' → ');
    }

    private function formatValue(mixed $value): string
    {
        if ($value === null || $value === '') {
            return 'Vide';
        }

        if (is_bool($value)) {
            return $value ? 'Oui' : 'Non';
        }

        if (is_array($value)) {
            $value = collect($value)
                ->map(fn (mixed $item, string|int $key) => is_scalar($item) || $item === null
                    ? $key.': '.$this->formatValue($item)
                    : $key.': '.json_encode($item, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
                ->implode(' | ');
        }

        $value = trim(strip_tags((string) $value));

        if (mb_strlen($value) > 220) {
            return mb_substr($value, 0, 220).'...';
        }

        return $value;
    }

    private function canRestore(ContentRevision $revision): bool
    {
        return match ($revision->content_type) {
            'block', 'item' => $revision->before !== null || ($revision->action === 'created' && $revision->after !== null),
            'collection' => $revision->before !== null,
            default => false,
        };
    }
}
