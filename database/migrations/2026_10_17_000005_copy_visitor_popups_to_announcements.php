<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Reprend les annonces visiteurs existantes (content_items « visitor_popups »)
 * dans le nouveau système d'annonces. Les lignes d'origine ne sont pas modifiées.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('content_items') || DB::table('announcements')->exists()) {
            return;
        }

        DB::table('content_items')
            ->where('collection_key', 'visitor_popups')
            ->orderBy('id')
            ->get()
            ->each(function (object $item): void {
                $meta = json_decode($item->meta ?? '[]', true) ?: [];
                $locale = $item->locale ?? 'fr';
                $translated = fn (?string $value) => $value === null || $value === '' ? null : json_encode([$locale => $value], JSON_UNESCAPED_UNICODE);
                $url = $item->link_url && $item->link_url !== '#' ? $item->link_url : null;

                DB::table('announcements')->insert([
                    'title' => $translated($item->title) ?? json_encode([$locale => 'Annonce'], JSON_UNESCAPED_UNICODE),
                    'message' => $translated($item->description),
                    'cta_label' => $url ? $translated($item->link_label) : null,
                    'cta_url' => $url,
                    'image' => $item->image ?: null,
                    // L'ancien popup s'affichait en fenêtre : on conserve ce comportement
                    'type' => 'important',
                    'status' => $item->is_active ? 'published' : (isset($meta['archived_at']) ? 'archived' : 'draft'),
                    'priority' => 1,
                    'starts_at' => null,
                    'ends_at' => null,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ]);
            });
    }

    public function down(): void
    {
        // Les données d'origine sont intactes : rien à restaurer.
    }
};
