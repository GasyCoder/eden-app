<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Support\EditorialDate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory, HasTranslations;

    public const TYPES = ['info', 'event', 'important', 'maintenance', 'urgent'];

    public const STATUSES = ['draft', 'published', 'archived'];

    /**
     * Présentation choisie automatiquement selon le type :
     * les messages critiques ouvrent une fenêtre, les autres un bandeau discret.
     */
    public const DISPLAY = [
        'info' => 'banner',
        'event' => 'banner',
        'maintenance' => 'banner',
        'important' => 'dialog',
        'urgent' => 'dialog',
    ];

    protected $fillable = [
        'title', 'message', 'cta_label', 'cta_url', 'image', 'type', 'status', 'priority', 'starts_at', 'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'message' => 'array',
            'cta_label' => 'array',
            'priority' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /** Publiée et dans sa période de diffusion. */
    public function scopeLive(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where(fn (Builder $query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }

    public function display(): string
    {
        return self::DISPLAY[$this->type] ?? 'banner';
    }

    /** État lisible dans l'administration. */
    public function state(): string
    {
        if ($this->status !== 'published') {
            return $this->status;
        }

        if ($this->starts_at?->isFuture()) {
            return 'scheduled';
        }

        if ($this->ends_at?->isPast()) {
            return 'expired';
        }

        return 'live';
    }

    public function toPublic(?string $locale = null): array
    {
        return [
            'id' => $this->id,
            // La clé change à chaque modification : une annonce mise à jour peut être revue
            'key' => $this->id.'-'.$this->updated_at?->timestamp,
            'title' => $this->translate('title', $locale),
            'message' => $this->translate('message', $locale),
            'cta_label' => $this->translate('cta_label', $locale),
            'cta_url' => $this->cta_url,
            'image' => $this->image,
            'type' => $this->type,
            'display' => $this->display(),
            'priority' => $this->priority,
        ];
    }

    public function toAdminRow(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->translate('title', 'fr'),
            'type' => $this->type,
            'display' => $this->display(),
            'status' => $this->status,
            'state' => $this->state(),
            'priority' => $this->priority,
            'starts_at' => $this->starts_at?->toIso8601String(),
            'ends_at' => $this->ends_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'locales' => $this->completeLocales(['title']),
        ];
    }

    public function toForm(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->translations('title'),
            'message' => $this->translations('message'),
            'cta_label' => $this->translations('cta_label'),
            'cta_url' => $this->cta_url ?? '',
            'image' => $this->image ?? '',
            'type' => $this->type,
            'status' => $this->status,
            'priority' => $this->priority,
            'starts_at' => EditorialDate::toInput($this->starts_at),
            'ends_at' => EditorialDate::toInput($this->ends_at),
        ];
    }
}
