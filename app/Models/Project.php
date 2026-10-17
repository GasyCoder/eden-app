<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Services\ImageVariantService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Projet de recherche ou de coopération. Les blocs structurés de la page de détail
 * (objectifs, partenaires, lots de travail…) sont stockés dans « details ».
 */
class Project extends Model
{
    use HasFactory, HasTranslations;

    public const STATUSES = ['draft', 'published'];

    /** Avancement affiché sur les cartes. */
    public const PROGRESS = ['upcoming', 'ongoing', 'completed'];

    /** Listes éditables de la page de détail. */
    public const DETAIL_LISTS = ['objectives', 'beneficiaries', 'partners', 'work_packages', 'facts'];

    protected $fillable = [
        'slug', 'title', 'acronym', 'subtitle', 'summary', 'body', 'cover_image', 'logo', 'progress',
        'team', 'funder', 'leader', 'start_year', 'end_year', 'external_url', 'details', 'status',
        'is_featured', 'position',
    ];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'subtitle' => 'array',
            'summary' => 'array',
            'body' => 'array',
            'details' => 'array',
            'start_year' => 'integer',
            'end_year' => 'integer',
            'is_featured' => 'boolean',
            'position' => 'integer',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position')->orderByDesc('is_featured')->orderBy('id');
    }

    public function isPublic(): bool
    {
        return $this->status === 'published';
    }

    public function period(): ?string
    {
        if (! $this->start_year && ! $this->end_year) {
            return null;
        }

        return trim(($this->start_year ?? '…').' - '.($this->end_year ?? '…'));
    }

    /** Une page de détail existe dès que le projet a un contenu propre. */
    public function hasDetailPage(): bool
    {
        $details = $this->details ?? [];

        return filled($this->translate('body', 'fr'))
            || collect(self::DETAIL_LISTS)->contains(fn (string $list) => ! empty($details[$list]));
    }

    public function detail(string $key, mixed $default = null): mixed
    {
        return ($this->details ?? [])[$key] ?? $default;
    }

    public function toCard(?string $locale = null): array
    {
        $prefix = $locale === 'en' ? '/en/projects/' : '/projets/';
        $internal = $this->hasDetailPage();

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->translate('title', $locale),
            'acronym' => $this->acronym,
            'subtitle' => $this->translate('subtitle', $locale),
            'summary' => $this->translate('summary', $locale),
            'cover_image' => $this->cover_image,
            'cover_sources' => app(ImageVariantService::class)->forUrl($this->cover_image),
            'logo' => $this->logo,
            'progress' => $this->progress,
            'team' => $this->team,
            'funder' => $this->funder,
            'leader' => $this->leader,
            'period' => $this->period(),
            'is_featured' => $this->is_featured,
            'url' => $internal ? $prefix.$this->slug : ($this->external_url ?: null),
            'is_external' => ! $internal && filled($this->external_url),
        ];
    }

    public function toDetail(?string $locale = null): array
    {
        $details = $this->details ?? [];

        return [
            ...$this->toCard($locale),
            'body' => $this->translate('body', $locale),
            'external_url' => $this->external_url,
            'objectives' => array_values(array_filter($details['objectives'] ?? [])),
            'beneficiaries' => array_values(array_filter($details['beneficiaries'] ?? [])),
            'partners' => array_values($details['partners'] ?? []),
            'work_packages' => array_values($details['work_packages'] ?? []),
            'facts' => array_values($details['facts'] ?? []),
            'funding_notice' => $details['funding_notice'] ?? null,
            'funding_logo' => $details['funding_logo'] ?? null,
            'gallery_image' => $details['gallery_image'] ?? null,
            'is_public' => $this->isPublic(),
        ];
    }

    public function toAdminRow(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->translate('title', 'fr'),
            'acronym' => $this->acronym,
            'cover_image' => $this->cover_image,
            'logo' => $this->logo,
            'progress' => $this->progress,
            'team' => $this->team,
            'funder' => $this->funder,
            'period' => $this->period(),
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'has_page' => $this->hasDetailPage(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'locales' => $this->completeLocales(['title']),
        ];
    }

    public function toForm(): array
    {
        $details = $this->details ?? [];

        return [
            'id' => $this->id,
            'slug' => $this->slug ?? '',
            'title' => $this->translations('title'),
            'subtitle' => $this->translations('subtitle'),
            'summary' => $this->translations('summary'),
            'body' => $this->translations('body'),
            'acronym' => $this->acronym ?? '',
            'cover_image' => $this->cover_image ?? '',
            'logo' => $this->logo ?? '',
            // « stage » : useForm() d'Inertia réserve déjà la propriété « progress »
            'stage' => $this->progress ?? 'ongoing',
            'team' => $this->team ?? '',
            'funder' => $this->funder ?? '',
            'leader' => $this->leader ?? '',
            'start_year' => $this->start_year,
            'end_year' => $this->end_year,
            'external_url' => $this->external_url ?? '',
            'objectives' => array_values($details['objectives'] ?? []),
            'beneficiaries' => array_values($details['beneficiaries'] ?? []),
            'partners' => array_values(array_map(fn ($partner) => [
                'name' => $partner['name'] ?? '', 'country' => $partner['country'] ?? '', 'role' => $partner['role'] ?? '',
                'logo' => $partner['logo'] ?? '', 'url' => $partner['url'] ?? '',
            ], $details['partners'] ?? [])),
            'work_packages' => array_values(array_map(fn ($package) => [
                'code' => $package['code'] ?? '', 'title' => $package['title'] ?? '', 'description' => $package['description'] ?? '', 'lead' => $package['lead'] ?? '',
            ], $details['work_packages'] ?? [])),
            'facts' => array_values(array_map(fn ($fact) => ['label' => $fact['label'] ?? '', 'value' => $fact['value'] ?? ''], $details['facts'] ?? [])),
            'funding_notice' => $details['funding_notice'] ?? '',
            'funding_logo' => $details['funding_logo'] ?? '',
            'gallery_image' => $details['gallery_image'] ?? '',
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'position' => $this->position,
        ];
    }

    public static function slugFor(string $value): string
    {
        return Str::slug($value) ?: 'projet';
    }
}
