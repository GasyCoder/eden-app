<?php

namespace App\Models;

use App\Contracts\Newsletterable;
use App\Models\Concerns\HasTranslations;
use App\Services\ImageVariantService;
use App\Support\EditorialDate;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model implements Newsletterable
{
    use HasFactory, HasTranslations;
    use SoftDeletes;

    public const STATUSES = ['draft', 'published', 'completed', 'archived', 'cancelled'];

    public const FORMATS = ['in_person', 'online', 'hybrid'];

    protected $fillable = [
        'slug', 'title', 'summary', 'body', 'cover_image', 'starts_at', 'ends_at', 'is_all_day', 'location',
        'format', 'registration_url', 'organizer', 'status', 'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'summary' => 'array',
            'body' => 'array',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_all_day' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    /** Publiés et états finaux consultables par le public. */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->whereIn('status', ['published', 'cancelled', 'completed', 'archived']);
    }

    /** À venir ou en cours : la fin (ou le début) n'est pas passée. */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereNotIn('status', ['completed', 'archived'])
            ->where(function (Builder $query): void {
                $query->where('ends_at', '>=', now())
                    ->orWhere(fn (Builder $query) => $query->whereNull('ends_at')->where('starts_at', '>=', now()->startOfDay()));
            })->orderBy('starts_at');
    }

    public function scopePast(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query->where('ends_at', '<', now())
                ->orWhere(fn (Builder $query) => $query->whereNull('ends_at')->where('starts_at', '<', now()->startOfDay()))
                ->orWhereIn('status', ['completed', 'archived']);
        })->orderByDesc('starts_at');
    }

    public function isPublic(): bool
    {
        return ! $this->trashed() && in_array($this->status, ['published', 'cancelled', 'completed', 'archived'], true);
    }

    public function isPast(): bool
    {
        return ($this->ends_at ?? $this->starts_at->copy()->endOfDay())->isPast();
    }

    public function toCard(?string $locale = null): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->translate('title', $locale),
            'summary' => $this->translate('summary', $locale),
            'cover_image' => $this->cover_image,
            'cover_sources' => app(ImageVariantService::class)->forUrl($this->cover_image),
            'starts_at' => $this->starts_at?->toIso8601String(),
            'ends_at' => $this->ends_at?->toIso8601String(),
            'is_all_day' => $this->is_all_day,
            'location' => $this->location,
            'format' => $this->format,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'is_past' => $this->isPast(),
            'registration_url' => $this->registration_url,
        ];
    }

    public function toDetail(?string $locale = null): array
    {
        return [
            ...$this->toCard($locale),
            'body' => $this->translate('body', $locale),
            'organizer' => $this->organizer,
            'is_public' => $this->isPublic(),
        ];
    }

    public function toAdminRow(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->translate('title', 'fr'),
            'starts_at' => $this->starts_at?->toIso8601String(),
            'ends_at' => $this->ends_at?->toIso8601String(),
            'is_all_day' => $this->is_all_day,
            'location' => $this->location,
            'format' => $this->format,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'is_past' => $this->isPast(),
            'cover_image' => $this->cover_image,
            'locales' => $this->completeLocales(['title']),
        ];
    }

    public function toForm(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->translations('title'),
            'summary' => $this->translations('summary'),
            'body' => $this->translations('body'),
            'cover_image' => $this->cover_image ?? '',
            'starts_at' => EditorialDate::toInput($this->starts_at),
            'ends_at' => EditorialDate::toInput($this->ends_at),
            'is_all_day' => $this->is_all_day,
            'location' => $this->location ?? '',
            'format' => $this->format,
            'registration_url' => $this->registration_url ?? '',
            'organizer' => $this->organizer ?? '',
            'status' => $this->status,
            'is_featured' => $this->is_featured,
        ];
    }

    public function newsletterReadyAt(): ?CarbonInterface
    {
        // Un événement est public dès sa publication (pas de date de mise en ligne)
        return $this->status === 'published' ? ($this->updated_at ?? now()) : null;
    }

    public function newsletterContent(string $locale): array
    {
        $en = $locale === 'en';
        $tz = EditorialDate::timezone();
        $start = $this->starts_at?->copy()->timezone($tz)->locale($en ? 'en' : 'fr');
        $date = $start ? ($this->is_all_day ? $start->isoFormat('dddd D MMMM YYYY') : $start->isoFormat($en ? 'dddd D MMMM YYYY [at] HH:mm' : 'dddd D MMMM YYYY [à] HH:mm')) : null;

        return [
            'kind' => $en ? 'Event' : 'Événement',
            'title' => (string) $this->translate('title', $locale),
            'excerpt' => $this->translate('summary', $locale),
            'image' => $this->cover_image,
            'url' => route($en ? 'events.show.en' : 'events.show', $this->slug),
            'details' => array_values(array_filter([$date ? ucfirst($date) : null, $this->location])),
            'cta' => $en ? 'See the event' : 'Voir l’événement',
        ];
    }
}
