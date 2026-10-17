<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('newsletter_subscriptions', function (Blueprint $table): void {
            $table->string('status', 20)->default('pending')->change();
            $table->string('verification_token', 64)->nullable()->unique();
            $table->timestamp('verification_sent_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
        });

        // Les anciens abonnements n'ont aucune preuve de confirmation : ne pas en inventer.
        DB::table('newsletter_subscriptions')->where('status', 'subscribed')->update(['status' => 'pending']);
    }

    public function down(): void
    {
        DB::table('newsletter_subscriptions')->whereIn('status', ['pending', 'pending_approval', 'rejected'])->update(['status' => 'unsubscribed']);

        Schema::table('newsletter_subscriptions', function (Blueprint $table): void {
            $table->dropUnique(['verification_token']);
            $table->dropColumn(['verification_token', 'verification_sent_at', 'email_verified_at', 'approved_at', 'rejected_at']);
            $table->string('status', 20)->default('subscribed')->change();
        });
    }
};
