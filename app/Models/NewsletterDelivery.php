<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Envoi d'une campagne à un abonné (pending, sent, failed, skipped). */
class NewsletterDelivery extends Model
{
    protected $fillable = ['newsletter_campaign_id', 'newsletter_subscription_id', 'status', 'sent_at', 'error'];

    protected function casts(): array
    {
        return ['sent_at' => 'datetime'];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(NewsletterCampaign::class, 'newsletter_campaign_id');
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(NewsletterSubscription::class, 'newsletter_subscription_id');
    }
}
