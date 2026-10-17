<?php

namespace App\Services;

use App\Models\Announcement;
use App\Support\ContentLocale;

/**
 * Données communes à toutes les pages publiques : contenus éditables (en-tête,
 * pied de page, sections), annonces en cours et droits d'édition.
 */
class PublicPageProps
{
    public function __construct(
        private readonly ContentRepository $content,
        private readonly ImageVariantService $images,
    ) {}

    /**
     * @param  array{fr:string,en:string}  $languageUrls
     */
    public function base(string $locale, array $languageUrls, array $extra = []): array
    {
        $blocks = $this->content->blocks($locale);
        $canEdit = (bool) request()->user()?->is_admin;

        return [
            'locale' => $locale,
            'locales' => ContentLocale::SUPPORTED,
            'languageUrls' => $languageUrls,
            'blocks' => $blocks,
            'blockMeta' => $this->content->blockMeta($locale),
            'collections' => $this->content->collections($locale),
            'announcements' => $this->announcements($locale),
            'performance' => [
                'siteLogo' => $this->images->forUrl(
                    $blocks['site']['logo'] ?? null,
                    ImageVariantService::LOGO_WIDTHS,
                    ImageVariantService::LOGO_SIZES,
                ),
            ],
            'canEdit' => $canEdit,
            ...$extra,
        ];
    }

    /**
     * Annonces en cours de diffusion, de la plus prioritaire à la moins prioritaire.
     *
     * @return array<int, array<string, mixed>>
     */
    public function announcements(string $locale): array
    {
        return Announcement::query()
            ->live()
            ->orderByDesc('priority')
            ->latest('updated_at')
            ->limit(5)
            ->get()
            ->map(fn (Announcement $announcement) => $announcement->toPublic($locale))
            ->values()
            ->all();
    }

    /**
     * URLs FR/EN d'une page à partir de ses deux noms de route.
     */
    public static function languageUrls(string $frRoute, string $enRoute, array $parameters = []): array
    {
        return [
            'fr' => route($frRoute, $parameters),
            'en' => route($enRoute, $parameters),
        ];
    }
}
