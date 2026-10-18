<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'posts', 'post_categories', 'events', 'projects', 'journals', 'journal_issues',
        'announcements', 'hero_slides', 'newsletter_subscriptions', 'users', 'content_items',
    ];

    public function up(): void
    {
        foreach ($this->tables as $name) {
            if (! Schema::hasColumn($name, 'deleted_at')) {
                Schema::table($name, fn (Blueprint $table) => $table->softDeletes());
            }
        }

        if (! Schema::hasColumn('journal_issues', 'deleted_with_journal')) {
            Schema::table('journal_issues', fn (Blueprint $table) => $table->boolean('deleted_with_journal')->default(false));
        }
    }

    public function down(): void
    {
        Schema::table('journal_issues', fn (Blueprint $table) => $table->dropColumn('deleted_with_journal'));

        foreach (array_reverse($this->tables) as $name) {
            Schema::table($name, fn (Blueprint $table) => $table->dropSoftDeletes());
        }
    }
};
