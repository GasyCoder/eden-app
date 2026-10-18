<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Services\ContentSanitizer;
use App\Services\RichTextSanitizer;
use App\Support\ContentLocale;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Outils partagés par les formulaires des modules éditoriaux
 * (actualités, revues, événements, annonces).
 */
trait HandlesEditorialInput
{
    /** Règles pour un champ traduisible : FR requis ou non, EN facultatif. */
    protected function translatableRules(string $field, bool $required = false, int $max = 255, bool $html = false): array
    {
        $type = $html ? ['string', 'max:200000'] : ['string', "max:{$max}"];

        return [
            $field => ['nullable', 'array'],
            "{$field}.fr" => [$required ? 'required' : 'nullable', ...$type],
            "{$field}.en" => ['nullable', ...$type],
        ];
    }

    /** Valeur JSON {fr, en} nettoyée (null si toutes les langues sont vides). */
    protected function translated(array $data, string $field, string $type = 'text'): ?array
    {
        $values = collect(ContentLocale::SUPPORTED)->mapWithKeys(function (string $locale) use ($data, $field, $type) {
            $value = $data[$field][$locale] ?? null;

            $clean = match ($type) {
                'html' => app(RichTextSanitizer::class)->clean($value),
                'textarea' => app(ContentSanitizer::class)->clean($value, 'textarea'),
                default => app(ContentSanitizer::class)->clean($value),
            };

            return [$locale => $clean === '' ? null : $clean];
        })->filter(fn ($value) => $value !== null);

        return $values->isEmpty() ? null : $values->all();
    }

    protected function plain(?string $value, string $type = 'text'): ?string
    {
        $clean = app(ContentSanitizer::class)->clean($value, $type);

        return $clean === '' || $clean === null ? null : $clean;
    }

    /** Lien interne (/…, #…) ou externe (http(s), mailto, tel). */
    protected function linkRule(): array
    {
        return ['nullable', 'string', 'max:2048', 'regex:/^(https?:\/\/|\/|#|mailto:|tel:)/i'];
    }

    protected function uniqueSlug(string $modelClass, ?string $requested, ?string $title, ?Model $ignore = null): string
    {
        $base = Str::slug($requested ?: (string) $title) ?: 'element';
        $base = Str::limit($base, 180, '');
        $slug = $base;
        $suffix = 2;

        $query = $modelClass::query();
        if (in_array(SoftDeletes::class, class_uses_recursive($modelClass), true)) {
            $query->withTrashed();
        }

        while ((clone $query)
            ->where('slug', $slug)
            ->when($ignore, fn ($query) => $query->whereKeyNot($ignore->getKey()))
            ->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
