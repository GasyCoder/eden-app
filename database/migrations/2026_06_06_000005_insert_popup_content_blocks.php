<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $blocks = [
            ['key' => 'popup.enabled', 'type' => 'number', 'value' => '0', 'position' => 1000],
            ['key' => 'popup.delay_seconds', 'type' => 'number', 'value' => '3', 'position' => 1001],
            ['key' => 'popup.title', 'type' => 'text', 'value' => 'Annonce importante', 'position' => 1002],
            ['key' => 'popup.message', 'type' => 'textarea', 'value' => "Une nouvelle information est disponible pour les visiteurs de l'École Doctorale EDEN.", 'position' => 1003],
            ['key' => 'popup.button_label', 'type' => 'text', 'value' => 'En savoir plus', 'position' => 1004],
            ['key' => 'popup.button_url', 'type' => 'link', 'value' => '#', 'position' => 1005],
            ['key' => 'popup.image', 'type' => 'image', 'value' => null, 'position' => 1006],
        ];

        foreach ($blocks as $block) {
            if (DB::table('content_blocks')->where('key', $block['key'])->doesntExist()) {
                DB::table('content_blocks')->insert([
                    ...$block,
                    'meta' => null,
                    'group' => 'popup',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('content_blocks')
            ->whereIn('key', [
                'popup.enabled',
                'popup.delay_seconds',
                'popup.title',
                'popup.message',
                'popup.button_label',
                'popup.button_url',
                'popup.image',
            ])
            ->delete();
    }
};
