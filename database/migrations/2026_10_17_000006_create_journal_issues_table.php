<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Numéros d'une revue (ex. MaToY, journal trimestriel de l'EDEN) : couverture,
 * fichier PDF, éditorial, sommaire par rubrique et chiffres du trimestre.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_issues', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('journal_id')->constrained()->cascadeOnDelete();
            $table->string('slug');
            $table->unsignedSmallInteger('number');
            $table->unsignedSmallInteger('year');
            $table->json('period')->nullable();
            $table->json('title')->nullable();
            $table->json('editorial')->nullable();
            $table->string('cover_image', 2048)->nullable();
            $table->string('pdf_url', 2048)->nullable();
            $table->unsignedInteger('pdf_size')->nullable();
            $table->unsignedSmallInteger('page_count')->nullable();
            $table->json('sections')->nullable();
            $table->json('stats')->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();

            $table->unique(['journal_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_issues');
    }
};
