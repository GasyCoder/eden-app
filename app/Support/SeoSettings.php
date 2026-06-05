<?php

namespace App\Support;

use App\Models\ContentBlock;

class SeoSettings
{
    /** Clés de content_blocks utilisées pour le référencement. */
    public const KEYS = [
        'seo.title',
        'seo.description',
        'seo.keywords',
        'seo.og_image',
        'site.favicon',
    ];

    public const DEFAULTS = [
        'title' => 'EDEN-UMG — École Doctorale Écosystèmes Naturels',
        'description' => "École Doctorale Écosystèmes Naturels (EDEN) de l'Université de Mahajanga : formation doctorale et recherche sur la biodiversité de Madagascar — équipes d'accueil, parcours doctoral, thématiques, projets et partenariats.",
        'keywords' => 'EDEN, École Doctorale, Université de Mahajanga, UMG, Écosystèmes Naturels, doctorat Madagascar, recherche biodiversité, thèse, Boeny, formation doctorale, écologie, conservation',
        'ogImage' => '/icons/og-image.png',
        'favicon' => '/icons/favicon-32.png',
    ];

    /**
     * Réglages SEO courants (valeurs en base, sinon valeurs par défaut).
     *
     * @return array{title:string,description:string,keywords:string,ogImage:string,favicon:string}
     */
    public static function all(): array
    {
        try {
            $blocks = ContentBlock::whereIn('key', self::KEYS)->pluck('value', 'key');
        } catch (\Throwable) {
            $blocks = collect();
        }

        return [
            'title' => self::value($blocks, 'seo.title', self::DEFAULTS['title']),
            'description' => self::value($blocks, 'seo.description', self::DEFAULTS['description']),
            'keywords' => self::value($blocks, 'seo.keywords', self::DEFAULTS['keywords']),
            'ogImage' => self::value($blocks, 'seo.og_image', self::DEFAULTS['ogImage']),
            'favicon' => self::value($blocks, 'site.favicon', self::DEFAULTS['favicon']),
        ];
    }

    private static function value($blocks, string $key, string $default): string
    {
        $value = $blocks[$key] ?? null;

        return ($value === null || $value === '') ? $default : $value;
    }
}
