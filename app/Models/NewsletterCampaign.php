<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Envoi d'un contenu (actualité, événement, numéro) à tous les abonnés actifs.
 * scheduled → en attente de publication ; queued/sending → en cours ; sent ; failed ; cancelled.
 */
class NewsletterCampaign extends Model
{
    public const STATUSES = ['scheduled', 'queued', 'sending', 'sent', 'failed', 'cancelled'];

    /** Statuts qui bloquent un nouvel envoi du même contenu. */
    public const ACTIVE_STATUSES = ['scheduled', 'queued', 'sending', 'sent'];

    protected $fillable = [
        'subject', 'notifiable_type', 'notifiable_id', 'status', 'scheduled_at', 'started_at', 'finished_at',
        'recipients_count', 'sent_count', 'failed_count', 'batch_id', 'last_error', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'recipients_count' => 'integer',
            'sent_count' => 'integer',
            'failed_count' => 'integer',
        ];
    }

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(NewsletterDelivery::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', self::ACTIVE_STATUSES);
    }

    /** Clôture l'envoi une fois tous les destinataires traités. */
    public function complete(): void
    {
        $this->refresh();
        $counts = $this->deliveries()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $this->forceFill([
            'sent_count' => (int) ($counts['sent'] ?? 0),
            'failed_count' => (int) ($counts['failed'] ?? 0),
            'status' => ($counts['sent'] ?? 0) === 0 && ($counts['failed'] ?? 0) > 0 ? 'failed' : 'sent',
            'finished_at' => now(),
        ])->save();
    }

    public function kindLabel(): string
    {
        return match ($this->notifiable_type) {
            Post::class => 'Actualité',
            Event::class => 'Événement',
            JournalIssue::class => 'Numéro de revue',
            default => 'Contenu',
        };
    }

    public function editUrl(): ?string
    {
        $item = $this->notifiable;

        return match (true) {
            $item instanceof Post => route('admin.posts.edit', $item),
            $item instanceof Event => route('admin.events.edit', $item),
            $item instanceof JournalIssue => route('admin.journals.issues.edit', [$item->journal_id, $item->id]),
            default => null,
        };
    }

    public function toSummary(): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'subject' => $this->subject,
            'scheduled_at' => $this->scheduled_at?->toIso8601String(),
            'started_at' => $this->started_at?->toIso8601String(),
            'finished_at' => $this->finished_at?->toIso8601String(),
            'recipients_count' => $this->recipients_count,
            'sent_count' => $this->sent_count,
            'failed_count' => $this->failed_count,
        ];
    }

    public function toAdminRow(): array
    {
        return [
            ...$this->toSummary(),
            'kind' => $this->kindLabel(),
            'edit_url' => $this->editUrl(),
            'author' => $this->author?->name,
            'last_error' => $this->last_error,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
