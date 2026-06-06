<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('content_blocks')->where('key', 'popup.image')->doesntExist()) {
            DB::table('content_blocks')->insert([
                'key' => 'popup.image',
                'type' => 'image',
                'value' => null,
                'meta' => null,
                'group' => 'popup',
                'position' => 1006,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('content_blocks')->where('key', 'popup.image')->delete();
    }
};
