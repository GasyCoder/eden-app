<?php

namespace App\Models;

use App\Contracts\Newsletterable;
use App\Models\Concerns\HasTranslations;
use App\Services\ImageVariantService;
use App\Services\RichTextSanitizer;
use App\Support\EditorialDate;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Numéro d'une revue (ex. « MaToY N° 01 · 2026 ») : couverture, PDF, éditorial,
 * sommaire par rubrique et chiffres clés de la période.
 */
class JournalIssue extends Model implements Newsletterable
{
    use HasFactory, HasTranslations;
    use SoftDeletes;

    public const STATUSES = ['draft', 'published'];

    /** Rubriques proposées dans l'administration (texte libre accepté). */
    public const RUBRICS = [
        'Actualités',
        'Formation & doctorat',
        'Soutenances',
        'Recherche & rayonnement',
        'Publications scientifiques',
        'Projets & partenariats',
        'L’EDEN en action',
        'L’EDEN en ligne',
    ];

    protected $fillable = [
        'journal_id', 'slug', 'number', 'year', 'period', 'title', 'editorial', 'cover_image',
        'pdf_url', 'pdf_size', 'page_count', 'sections', 'stats', 'status', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'period' => 'array',
            'title' => 'array',
            'editorial' => 'array',
            'sections' => 'array',
            'stats' => 'array',
            'number' => 'integer',
            'year' => 'integer',
            'pdf_size' => 'integer',
            'page_count' => 'integer',
            'published_at' => 'datetime',
            'deleted_with_journal' => 'boolean',
        ];
    }

    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query->orderByDesc('year')->orderByDesc('number')->orderByDesc('id');
    }

    public function isPublic(): bool
    {
        return ! $this->trashed() && $this->status === 'published' && $this->published_at !== null && $this->published_at->lte(now());
    }

    /** « N° 01 · 2026 » */
    public function label(): string
    {
        return sprintf('N° %02d · %d', $this->number, $this->year);
    }

    public function excerpt(?string $locale = null, int $limit = 260): ?string
    {
        return Str::limit(app(RichTextSanitizer::class)->plainText($this->translate('editorial', $locale)), $limit) ?: null;
    }

    /** Sommaire : rubriques dans l'ordre d'apparition, avec leurs articles. */
    public function contents(): array
    {
        return collect($this->sections ?? [])
            ->filter(fn ($section) => filled($section['title'] ?? null))
            ->groupBy(fn ($section) => trim((string) ($section['rubric'] ?? '')) ?: 'Au sommaire')
            ->map(fn ($items, $rubric) => [
                'rubric' => $rubric,
                'items' => $items->map(fn ($section) => [
                    'title' => $section['title'],
                    'summary' => $section['summary'] ?? null,
                    'image' => $section['image'] ?? null,
                    'page' => $section['page'] ?? null,
                ])->values()->all(),
            ])
            ->values()
            ->all();
    }

    public function toCard(?string $locale = null): array
    {
        $journal = $this->journal;

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'label' => $this->label(),
            'number' => $this->number,
            'year' => $this->year,
            'period' => $this->translate('period', $locale),
            'title' => $this->translate('title', $locale),
            'excerpt' => $this->excerpt($locale),
            'cover_image' => $this->cover_image,
            'cover_sources' => app(ImageVariantService::class)->forUrl($this->cover_image),
            'pdf_url' => $this->pdf_url,
            'pdf_size' => $this->pdf_size,
            'page_count' => $this->page_count,
            'published_at' => $this->published_at?->toIso8601String(),
            'rubrics' => collect($this->contents())->pluck('rubric')->values()->all(),
            'journal' => $journal ? [
                'slug' => $journal->slug,
                'title' => $journal->translate('title', $locale),
                'frequency' => $journal->translate('frequency', $locale),
            ] : null,
        ];
    }

    public function toDetail(?string $locale = null): array
    {
        return [
            ...$this->toCard($locale),
            'editorial' => $this->translate('editorial', $locale),
            'contents' => $this->contents(),
            'stats' => collect($this->stats ?? [])
                ->filter(fn ($stat) => filled($stat['label'] ?? null) && is_numeric($stat['value'] ?? null))
                ->map(fn ($stat) => ['label' => $stat['label'], 'value' => (float) $stat['value'] + 0])
                ->values()->all(),
            'is_public' => $this->isPublic(),
        ];
    }

    public function toAdminRow(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'label' => $this->label(),
            'period' => $this->translate('period', 'fr'),
            'title' => $this->translate('title', 'fr'),
            'status' => $this->status,
            'cover_image' => $this->cover_image,
            'has_pdf' => filled($this->pdf_url),
            'sections_count' => count($this->sections ?? []),
            'published_at' => $this->published_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    public function toForm(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug ?? '',
            'number' => $this->number,
            'year' => $this->year,
            'period' => $this->translations('period'),
            'title' => $this->translations('title'),
            'editorial' => $this->translations('editorial'),
            'cover_image' => $this->cover_image ?? '',
            'pdf_url' => $this->pdf_url ?? '',
            'pdf_size' => $this->pdf_size,
            'page_count' => $this->page_count,
            'sections' => collect($this->sections ?? [])->map(fn ($section) => [
                'rubric' => $section['rubric'] ?? '',
                'title' => $section['title'] ?? '',
                'summary' => $section['summary'] ?? '',
                'image' => $section['image'] ?? '',
                'page' => $section['page'] ?? null,
            ])->values()->all(),
            'stats' => collect($this->stats ?? [])->map(fn ($stat) => [
                'label' => $stat['label'] ?? '',
                'value' => $stat['value'] ?? null,
            ])->values()->all(),
            'status' => $this->status,
            'published_at' => $this->published_at ? $this->published_at->copy()->timezone(EditorialDate::timezone())->format('Y-m-d') : '',
        ];
    }

    public function newsletterReadyAt(): ?CarbonInterface
    {
        return $this->status === 'published' && $this->journal?->isPublic() ? $this->published_at : null;
    }

    public function newsletterContent(string $locale): array
    {
        $en = $locale === 'en';
        $journal = $this->journal;

        return [
            'kind' => $journal?->translate('title', $locale) ?? ($en ? 'Journal' : 'Revue'),
            'title' => trim(($journal?->translate('title', $locale) ?? '').' '.$this->label().($this->translate('period', $locale) ? ' · '.$this->translate('period', $locale) : '')),
            'excerpt' => $this->excerpt($locale, 320),
            'image' => $this->cover_image ?: $journal?->cover_image,
            'url' => route($en ? 'journals.issues.show.en' : 'journals.issues.show', [$journal?->slug, $this->slug]),
            'details' => collect($this->contents())->pluck('rubric')->take(6)->values()->all(),
            'cta' => $en ? 'Read this issue' : 'Lire ce numéro',
        ];
    }
}
