<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Newsletter : statut et désinscription des abonnés, envois (campagnes) liés à une
 * actualité, un événement ou un numéro de revue, et suivi de chaque destinataire.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('newsletter_subscriptions', function (Blueprint $table): void {
            $table->string('name')->nullable()->after('email');
            $table->string('status', 20)->default('subscribed')->after('name')->index();
            $table->string('locale', 5)->default('fr')->after('status');
            $table->string('source', 20)->default('site')->after('locale');
            $table->string('token', 64)->nullable()->unique()->after('source');
            $table->timestamp('unsubscribed_at')->nullable()->after('subscribed_at');
        });

        DB::table('newsletter_subscriptions')->whereNull('token')->orderBy('id')->each(function ($row): void {
            DB::table('newsletter_subscriptions')->where('id', $row->id)->update(['token' => Str::random(48)]);
        });

        Schema::create('newsletter_campaigns', function (Blueprint $table): void {
            $table->id();
            $table->string('subject');
            $table->nullableMorphs('notifiable');
            $table->string('status', 20)->default('scheduled')->index();
            $table->timestamp('scheduled_at')->nullable()->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('recipients_count')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->string('batch_id')->nullable();
            $table->text('last_error')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('newsletter_deliveries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('newsletter_campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('newsletter_subscription_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('pending')->index();
            $table->timestamp('sent_at')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->unique(['newsletter_campaign_id', 'newsletter_subscription_id'], 'newsletter_delivery_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_deliveries');
        Schema::dropIfExists('newsletter_campaigns');

        Schema::table('newsletter_subscriptions', function (Blueprint $table): void {
            $table->dropUnique(['token']);
            $table->dropColumn(['name', 'status', 'locale', 'source', 'token', 'unsubscribed_at']);
        });
    }
};
