<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Services\ImageVariantService;
use App\Support\EditorialDate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Diapositive du héro de l'accueil, éventuellement reliée à un contenu du site.
 */
class HeroSlide extends Model
{
    use HasFactory, HasTranslations;
    use SoftDeletes;

    /** Type de lien => modèle relié (null : aucun lien ou adresse libre). */
    public const LINK_TYPES = [
        'none' => null,
        'post' => Post::class,
        'event' => Event::class,
        'announcement' => Announcement::class,
        'project' => Project::class,
        'journal_issue' => JournalIssue::class,
        'url' => null,
    ];

    /** Cadrage de l'image (utile sur mobile, où le format est plus haut). */
    public const FOCAL_POINTS = ['center', 'top', 'bottom', 'left', 'right'];

    protected $fillable = [
        'image', 'focal_point', 'title', 'subtitle', 'cta_label', 'link_type', 'linkable_type', 'linkable_id',
        'url', 'is_active', 'starts_at', 'ends_at', 'position',
    ];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'subtitle' => 'array',
            'cta_label' => 'array',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'position' => 'integer',
        ];
    }

    public function linkable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeLive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(fn (Builder $query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position')->orderBy('id');
    }

    /** « en ligne », « programmée », « expirée » ou « masquée ». */
    public function state(): string
    {
        return match (true) {
            ! $this->is_active => 'hidden',
            $this->starts_at?->isFuture() => 'scheduled',
            $this->ends_at?->isPast() => 'expired',
            default => 'live',
        };
    }

    /**
     * Lien public résolu : null si aucun lien ou si le contenu relié n'est plus public.
     *
     * @return array{url:string,label:string,external:bool,kind:string}|null
     */
    public function link(?string $locale = null): ?array
    {
        $en = $locale === 'en';
        $item = $this->linkable;
        $label = $this->translate('cta_label', $locale);

        [$url, $default] = match ($this->link_type) {
            'url' => [$this->url, $en ? 'Learn more' : 'En savoir plus'],
            'post' => $item instanceof Post && $item->isPublic() ? [route($en ? 'news.show.en' : 'news.show', $item->slug), $en ? 'Read the article' : 'Lire l’article'] : [null, null],
            'event' => $item instanceof Event && $item->isPublic() ? [route($en ? 'events.show.en' : 'events.show', $item->slug), $en ? 'See the event' : 'Voir l’événement'] : [null, null],
            'announcement' => $item instanceof Announcement && Announcement::query()->live()->whereKey($item->id)->exists() && $item->cta_url ? [$item->cta_url, $item->translate('cta_label', $locale) ?: ($en ? 'Learn more' : 'En savoir plus')] : [null, null],
            'project' => $item instanceof Project && $item->isPublic() ? [$item->toCard($locale)['url'], $en ? 'Discover the project' : 'Découvrir le projet'] : [null, null],
            'journal_issue' => $item instanceof JournalIssue && $item->isPublic() && $item->journal?->isPublic() ? [route($en ? 'journals.issues.show.en' : 'journals.issues.show', [$item->journal->slug, $item->slug]), $en ? 'Read this issue' : 'Lire ce numéro'] : [null, null],
            default => [null, null],
        };

        if (! $url) {
            return null;
        }

        return [
            'url' => $url,
            'label' => $label ?: $default,
            'external' => (bool) preg_match('/^https?:\/\//i', $url) && ! str_starts_with($url, url('/')),
            'kind' => $this->link_type,
        ];
    }

    /** Titre et image du contenu relié, utilisés quand la diapositive n'a pas les siens. */
    private function linkedFallback(?string $locale): array
    {
        $item = $this->linkable;

        return match (true) {
            $item instanceof Post, $item instanceof Event, $item instanceof Project => [$item->translate('title', $locale), $item->cover_image],
            $item instanceof Announcement => [$item->translate('title', $locale), $item->image],
            $item instanceof JournalIssue => [trim(($item->journal?->translate('title', $locale) ?? '').' '.$item->label()), $item->cover_image],
            default => [null, null],
        };
    }

    public function toPublic(?string $locale = null): ?array
    {
        [$fallbackTitle, $fallbackImage] = $this->linkedFallback($locale);
        $image = $this->image ?: $fallbackImage;

        if (! $image) {
            return null;
        }

        return [
            'key' => 'slide-'.$this->id,
            'id' => $this->id,
            'image' => $image,
            'sources' => app(ImageVariantService::class)->forUrl($image, ImageVariantService::HERO_WIDTHS, ImageVariantService::HERO_SIZES),
            'focal' => $this->focal_point,
            'title' => $this->translate('title', $locale) ?: $fallbackTitle,
            'subtitle' => $this->translate('subtitle', $locale),
            'link' => $this->link($locale),
        ];
    }

    public function toAdminRow(): array
    {
        [$fallbackTitle, $fallbackImage] = $this->linkedFallback('fr');

        return [
            'id' => $this->id,
            'image' => $this->image ?: $fallbackImage,
            'title' => $this->translate('title', 'fr') ?: $fallbackTitle,
            'subtitle' => $this->translate('subtitle', 'fr'),
            'link_type' => $this->link_type,
            'link_target' => $fallbackTitle ?? $this->url,
            'link_ok' => $this->link_type === 'none' || $this->link('fr') !== null,
            'is_active' => $this->is_active,
            'state' => $this->state(),
            'starts_at' => $this->starts_at?->toIso8601String(),
            'ends_at' => $this->ends_at?->toIso8601String(),
        ];
    }

    public function toForm(): array
    {
        return [
            'id' => $this->id,
            'image' => $this->image ?? '',
            'focal_point' => $this->focal_point ?? 'center',
            'title' => $this->translations('title'),
            'subtitle' => $this->translations('subtitle'),
            'cta_label' => $this->translations('cta_label'),
            'link_type' => $this->link_type ?? 'none',
            'linkable_id' => $this->linkable_id,
            'url' => $this->url ?? '',
            'is_active' => $this->is_active ?? true,
            'starts_at' => EditorialDate::toInput($this->starts_at),
            'ends_at' => EditorialDate::toInput($this->ends_at),
        ];
    }
}
