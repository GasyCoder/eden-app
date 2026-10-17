<?php

namespace App\Models\Concerns;

use App\Support\ContentLocale;

/**
 * Champs traduisibles stockés en JSON : {"fr": "…", "en": "…"}.
 * Le français sert de repli quand une traduction manque.
 *
 * Le modèle déclare : protected array $translatable = ['title', …];
 * (les colonnes doivent être castées en 'array').
 */
trait HasTranslations
{
    public function translate(string $field, ?string $locale = null): ?string
    {
        $values = $this->getAttribute($field);

        if (! is_array($values)) {
            return $values;
        }

        $locale = ContentLocale::normalize($locale ?? app()->getLocale());
        $value = $values[$locale] ?? null;

        if ($value === null || $value === '') {
            $value = $values[ContentLocale::DEFAULT] ?? null;
        }

        return $value === '' ? null : $value;
    }

    public function hasTranslation(string $field, string $locale): bool
    {
        $values = $this->getAttribute($field);

        return is_array($values) && filled($values[$locale] ?? null);
    }

    /**
     * Valeurs brutes {fr, en} pour les formulaires d'administration.
     *
     * @return array<string, string>
     */
    public function translations(string $field): array
    {
        $values = $this->getAttribute($field);

        return collect(ContentLocale::SUPPORTED)
            ->mapWithKeys(fn (string $locale) => [$locale => is_array($values) ? (string) ($values[$locale] ?? '') : ''])
            ->all();
    }

    /**
     * Langues dans lesquelles tous les champs traduisibles requis sont renseignés.
     *
     * @return array<int, string>
     */
    public function completeLocales(array $requiredFields): array
    {
        return collect(ContentLocale::SUPPORTED)
            ->filter(fn (string $locale) => collect($requiredFields)->every(fn (string $field) => $this->hasTranslation($field, $locale)))
            ->values()
            ->all();
    }
}
