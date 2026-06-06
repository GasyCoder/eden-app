<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('content_items') || !Schema::hasTable('content_blocks')) {
            return;
        }

        if (DB::table('content_items')->where('collection_key', 'visitor_popups')->exists()) {
            return;
        }

        $blocks = DB::table('content_blocks')
            ->whereIn('key', [
                'popup.enabled',
                'popup.delay_seconds',
                'popup.title',
                'popup.message',
                'popup.button_label',
                'popup.button_url',
                'popup.image',
            ])
            ->get()
            ->keyBy('key');

        $imageMeta = json_decode((string) ($blocks->get('popup.image')->meta ?? ''), true);
        $meta = array_filter([
            'delay_seconds' => (int) ($blocks->get('popup.delay_seconds')->value ?? 3),
            'object_position_x' => $imageMeta['object_position_x'] ?? null,
            'object_position_y' => $imageMeta['object_position_y'] ?? null,
            'object_zoom' => $imageMeta['object_zoom'] ?? null,
        ], static fn ($value) => $value !== null);

        DB::table('content_items')->insert([
            'collection_key' => 'visitor_popups',
            'title' => $blocks->get('popup.title')->value ?? 'Annonce importante',
            'description' => $blocks->get('popup.message')->value ?? "Une nouvelle information est disponible pour les visiteurs de l'École Doctorale EDEN.",
            'image' => $blocks->get('popup.image')->value ?? null,
            'link_url' => $blocks->get('popup.button_url')->value ?? '#',
            'link_label' => $blocks->get('popup.button_label')->value ?? 'En savoir plus',
            'meta' => json_encode($meta),
            'position' => 0,
            'is_active' => (string) ($blocks->get('popup.enabled')->value ?? '0') === '1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        if (!Schema::hasTable('content_items')) {
            return;
        }

        DB::table('content_items')->where('collection_key', 'visitor_popups')->delete();
    }
};
