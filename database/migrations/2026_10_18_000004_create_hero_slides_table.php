<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Diapositives du héro de l'accueil, gérées dans /admin/hero. Chaque diapositive peut
 * renvoyer vers une actualité, un événement, une annonce, un projet, un numéro de
 * revue ou une adresse libre. Reprend la photo principale (hero.image) et la collection
 * « hero_slides » sans les supprimer.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hero_slides', function (Blueprint $table): void {
            $table->id();
            $table->string('image', 2048)->nullable();
            $table->string('focal_point', 20)->default('center');
            $table->json('title')->nullable();
            $table->json('subtitle')->nullable();
            $table->json('cta_label')->nullable();
            $table->string('link_type', 30)->default('none');
            $table->nullableMorphs('linkable');
            $table->string('url', 2048)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->integer('position')->default(0);
            $table->timestamps();
        });

        $now = now();
        $position = 0;
        $block = fn (string $key, string $locale = 'fr') => DB::table('content_blocks')->where('key', $key)->where('locale', $locale)->value('value');

        if ($image = $block('hero.image')) {
            DB::table('hero_slides')->insert([
                'image' => $image,
                'title' => json_encode(array_filter(['fr' => $block('hero.image_meta'), 'en' => $block('hero.image_meta', 'en')])) ?: null,
                'subtitle' => json_encode(array_filter(['fr' => $block('hero.image_caption'), 'en' => $block('hero.image_caption', 'en')])) ?: null,
                'link_type' => 'none',
                'is_active' => true,
                'position' => $position++,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $english = DB::table('content_items')->where('collection_key', 'hero_slides')->where('locale', 'en')->get()->keyBy('image');

        foreach (DB::table('content_items')->where('collection_key', 'hero_slides')->where('locale', 'fr')->orderBy('position')->orderBy('id')->get() as $item) {
            $en = $english->get($item->image);

            DB::table('hero_slides')->insert([
                'image' => $item->image,
                'title' => json_encode(array_filter(['fr' => $item->title, 'en' => $en?->title])) ?: null,
                'subtitle' => json_encode(array_filter(['fr' => $item->subtitle, 'en' => $en?->subtitle])) ?: null,
                'link_type' => $item->link_url ? 'url' : 'none',
                'url' => $item->link_url ?: null,
                'is_active' => (bool) $item->is_active,
                'position' => $position++,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_slides');
    }
};
