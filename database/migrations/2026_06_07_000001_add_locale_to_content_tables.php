<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('content_blocks') && ! Schema::hasColumn('content_blocks', 'locale')) {
            Schema::table('content_blocks', function (Blueprint $table): void {
                $table->string('locale', 5)->default('fr')->after('key')->index();
            });

            $this->dropContentBlockKeyUnique();

            Schema::table('content_blocks', function (Blueprint $table): void {
                $table->unique(['key', 'locale'], 'content_blocks_key_locale_unique');
            });
        }

        if (Schema::hasTable('content_items') && ! Schema::hasColumn('content_items', 'locale')) {
            Schema::table('content_items', function (Blueprint $table): void {
                $table->string('locale', 5)->default('fr')->after('collection_key')->index();
                $table->index(['collection_key', 'locale'], 'content_items_collection_locale_index');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('content_blocks') && Schema::hasColumn('content_blocks', 'locale')) {
            DB::table('content_blocks')->where('locale', '<>', 'fr')->delete();

            Schema::table('content_blocks', function (Blueprint $table): void {
                $table->dropUnique('content_blocks_key_locale_unique');
                $table->dropIndex(['locale']);
                $table->dropColumn('locale');
            });

            Schema::table('content_blocks', function (Blueprint $table): void {
                $table->unique('key');
            });
        }

        if (Schema::hasTable('content_items') && Schema::hasColumn('content_items', 'locale')) {
            DB::table('content_items')->where('locale', '<>', 'fr')->delete();

            Schema::table('content_items', function (Blueprint $table): void {
                $table->dropIndex('content_items_collection_locale_index');
                $table->dropIndex(['locale']);
                $table->dropColumn('locale');
            });
        }
    }

    private function dropContentBlockKeyUnique(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS content_blocks_key_unique');

            return;
        }

        Schema::table('content_blocks', function (Blueprint $table): void {
            $table->dropUnique('content_blocks_key_unique');
        });
    }
};
