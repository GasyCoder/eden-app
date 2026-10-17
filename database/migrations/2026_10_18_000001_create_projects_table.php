<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Projets de recherche et de coopération (BIODIVERS-BOENY, RAVENALA…), gérés dans
 * l'administration avec une page de détail publique /projets/{slug}.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->json('title');
            $table->string('acronym', 60)->nullable();
            $table->json('subtitle')->nullable();
            $table->json('summary')->nullable();
            $table->json('body')->nullable();
            $table->string('cover_image', 2048)->nullable();
            $table->string('logo', 2048)->nullable();
            $table->string('progress', 20)->default('ongoing')->index();
            $table->string('team', 120)->nullable();
            $table->string('funder')->nullable();
            $table->string('leader')->nullable();
            $table->unsignedSmallInteger('start_year')->nullable();
            $table->unsignedSmallInteger('end_year')->nullable();
            $table->string('external_url', 2048)->nullable();
            $table->json('details')->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->boolean('is_featured')->default(false);
            $table->integer('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
