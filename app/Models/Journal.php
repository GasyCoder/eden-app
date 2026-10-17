<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Services\ImageVariantService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Revue scientifique liée à l'EDEN (catalogue éditorial).
 * Ses numéros (couverture, PDF, sommaire) vivent dans journal_issues.
 */
class Journal extends Model
{
    use HasFactory, HasTranslations;

    /** active = publiée et en activité ; archived = visible mais plus éditée. */
    public const STATUSES = ['draft', 'active', 'archived'];

    protected $fillable = [
        'slug', 'title', 'description', 'domain', 'cover_image', 'issn', 'external_url', 'frequency',
        'editor', 'language', 'status', 'is_featured', 'position',
    ];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'description' => 'array',
            'frequency' => 'array',
            'is_featured' => 'boolean',
            'position' => 'integer',
        ];
    }

    public function issues(): HasMany
    {
        return $this->hasMany(JournalIssue::class);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->whereIn('status', ['active', 'archived']);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('is_featured')->orderBy('position')->orderBy('id');
    }

    public function isPublic(): bool
    {
        return in_array($this->status, ['active', 'archived'], true);
    }

    public function toCard(?string $locale = null): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->translate('title', $locale),
            'summary' => Str::limit(strip_tags((string) $this->translate('description', $locale)), 180) ?: null,
            'domain' => $this->domain,
            'cover_image' => $this->cover_image,
            'cover_sources' => app(ImageVariantService::class)->forUrl($this->cover_image),
            'issn' => $this->issn,
            'frequency' => $this->translate('frequency', $locale),
            'language' => $this->language,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'external_url' => $this->external_url,
            'latest_issue' => $this->latestIssueLabel(),
        ];
    }

    /** « N° 01 · 2026 » du dernier numéro publié (null si la revue n'a pas de numéro). */
    public function latestIssueLabel(): ?string
    {
        $issue = $this->relationLoaded('issues')
            ? $this->issues->filter->isPublic()->sortByDesc(fn (JournalIssue $issue) => [$issue->year, $issue->number])->first()
            : $this->issues()->published()->latestFirst()->first();

        return $issue?->label();
    }

    public function toDetail(?string $locale = null): array
    {
        return [
            ...$this->toCard($locale),
            'description' => $this->translate('description', $locale),
            'editor' => $this->editor,
            'is_public' => $this->isPublic(),
        ];
    }

    public function toAdminRow(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->translate('title', 'fr'),
            'domain' => $this->domain,
            'issn' => $this->issn,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'cover_image' => $this->cover_image,
            'issues_count' => $this->issues_count ?? $this->issues()->count(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'locales' => $this->completeLocales(['title']),
        ];
    }

    public function toForm(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->translations('title'),
            'description' => $this->translations('description'),
            'frequency' => $this->translations('frequency'),
            'domain' => $this->domain ?? '',
            'cover_image' => $this->cover_image ?? '',
            'issn' => $this->issn ?? '',
            'external_url' => $this->external_url ?? '',
            'editor' => $this->editor ?? '',
            'language' => $this->language ?? '',
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'position' => $this->position,
        ];
    }
}
