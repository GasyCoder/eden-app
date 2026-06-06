<?php

namespace App\Support;

use App\Models\ContentBlock;

class ThemeSettings
{
    /**
     * Sections dont le fond est personnalisable (clé => libellé).
     */
    public const SECTIONS = [
        'hero' => 'Accueil (bannière)',
        'about' => 'À propos',
        'mission' => 'Mission / Vision',
        'direction' => 'Mot de la direction',
        'organisation' => "Équipes d'accueil",
        'formation' => 'Parcours doctoral',
        'recherche' => 'Recherche',
        'chiffres' => 'Chiffres clés',
        'partenaires' => 'Partenaires',
        'projets' => 'Projets',
        'contact' => 'Contact',
        'footer' => 'Pied de page',
    ];

    public static function key(string $section): string
    {
        return 'theme.bg_'.$section;
    }

    /**
     * Couleurs enregistrées par section (vide si non personnalisée).
     *
     * @return array<string,string>
     */
    public static function all(): array
    {
        try {
            $stored = ContentBlock::query()
                ->whereIn('key', array_map(fn ($s) => self::key($s), array_keys(self::SECTIONS)))
                ->pluck('value', 'key');
        } catch (\Throwable) {
            $stored = collect();
        }

        $colors = [];

        foreach (array_keys(self::SECTIONS) as $section) {
            $colors[$section] = (string) ($stored[self::key($section)] ?? '');
        }

        return $colors;
    }

    /**
     * Variables CSS à injecter (uniquement les couleurs personnalisées).
     *
     * @return array<string,string>
     */
    public static function cssVariables(): array
    {
        $vars = [];

        foreach (self::all() as $section => $color) {
            if ($color !== '') {
                $vars['--bg-'.$section] = $color;
            }
        }

        return $vars;
    }
}
