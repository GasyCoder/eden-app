<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catalogue des revues. Les volumes, numéros, articles (DOI, auteurs) pourront
 * être ajoutés plus tard dans des tables liées par journal_id.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journals', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->json('title');
            $table->json('description')->nullable();
            $table->string('domain')->nullable()->index();
            $table->string('cover_image', 2048)->nullable();
            $table->string('issn', 20)->nullable();
            $table->string('external_url', 2048)->nullable();
            $table->json('frequency')->nullable();
            $table->string('editor')->nullable();
            $table->string('language', 60)->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->boolean('is_featured')->default(false);
            $table->integer('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journals');
    }
};
