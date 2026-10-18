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

class Post extends Model implements Newsletterable
{
    use HasFactory, HasTranslations;
    use SoftDeletes;

    public const STATUSES = ['draft', 'published', 'archived'];

    protected $fillable = [
        'slug', 'title', 'excerpt', 'body', 'cover_image', 'post_category_id', 'author_id', 'author_name',
        'status', 'published_at', 'is_featured', 'seo_title', 'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'excerpt' => 'array',
            'body' => 'array',
            'seo_title' => 'array',
            'meta_description' => 'array',
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'post_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /** Publié et dont la date de publication est atteinte. */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /** Actualités publiées ou archivées consultables à leur date de publication. */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->whereIn('status', ['published', 'archived'])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeLatestPublished(Builder $query): Builder
    {
        return $query->orderByDesc('published_at')->orderByDesc('id');
    }

    public function isPublic(): bool
    {
        return ! $this->trashed() && in_array($this->status, ['published', 'archived'], true) && $this->published_at !== null && $this->published_at->lte(now());
    }

    public function authorLabel(): ?string
    {
        return $this->author_name ?: $this->author?->name;
    }

    public function excerptFor(?string $locale = null): ?string
    {
        return $this->translate('excerpt', $locale)
            ?: (Str::limit(app(RichTextSanitizer::class)->plainText($this->translate('body', $locale)), 200) ?: null);
    }

    public function readingMinutes(?string $locale = null): int
    {
        $words = str_word_count(app(RichTextSanitizer::class)->plainText($this->translate('body', $locale)));

        return max(1, (int) ceil($words / 200));
    }

    public function toCard(?string $locale = null): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->translate('title', $locale),
            'excerpt' => $this->excerptFor($locale),
            'cover_image' => $this->cover_image,
            'cover_sources' => app(ImageVariantService::class)->forUrl($this->cover_image),
            'category' => $this->category?->toOption($locale),
            'author' => $this->authorLabel(),
            'published_at' => $this->published_at?->toIso8601String(),
            'is_featured' => $this->is_featured,
            'status' => $this->status,
            'reading_minutes' => $this->readingMinutes($locale),
        ];
    }

    public function toDetail(?string $locale = null): array
    {
        return [
            ...$this->toCard($locale),
            'body' => $this->translate('body', $locale),
            'seo_title' => $this->translate('seo_title', $locale) ?: $this->translate('title', $locale),
            'meta_description' => $this->translate('meta_description', $locale) ?: $this->excerptFor($locale),
            'is_public' => $this->isPublic(),
        ];
    }

    public function toAdminRow(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->translate('title', 'fr'),
            'category' => $this->category?->translate('name', 'fr'),
            'author' => $this->authorLabel(),
            'status' => $this->status,
            'is_scheduled' => $this->status === 'published' && $this->published_at?->isFuture(),
            'published_at' => $this->published_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'is_featured' => $this->is_featured,
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
            'excerpt' => $this->translations('excerpt'),
            'body' => $this->translations('body'),
            'seo_title' => $this->translations('seo_title'),
            'meta_description' => $this->translations('meta_description'),
            'cover_image' => $this->cover_image ?? '',
            'post_category_id' => $this->post_category_id,
            'author_name' => $this->author_name ?? '',
            'status' => $this->status,
            'published_at' => EditorialDate::toInput($this->published_at),
            'is_featured' => $this->is_featured,
        ];
    }

    public function newsletterReadyAt(): ?CarbonInterface
    {
        return $this->status === 'published' ? $this->published_at : null;
    }

    public function newsletterContent(string $locale): array
    {
        $en = $locale === 'en';

        return [
            'kind' => $en ? 'News' : 'Actualité',
            'title' => (string) $this->translate('title', $locale),
            'excerpt' => $this->excerptFor($locale),
            'image' => $this->cover_image,
            'url' => route($en ? 'news.show.en' : 'news.show', $this->slug),
            'details' => array_values(array_filter([$this->category?->translate('name', $locale)])),
            'cta' => $en ? 'Read the article' : 'Lire l’article',
        ];
    }
}
