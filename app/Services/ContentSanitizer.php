<?php

namespace App\Services;

class ContentSanitizer
{
    public function clean(?string $value, string $type = 'text'): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        if (in_array($type, ['image', 'link'], true) && preg_match('/\bsrc\s*=\s*(["\'])(.*?)\1/i', $value, $matches)) {
            $value = $matches[2];
        }

        if ($type === 'html') {
            $value = strip_tags($value, '<p><br><strong><em><b><i><ul><ol><li><a>');
            $value = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $value) ?? $value;
            $value = preg_replace('/javascript\s*:/i', '', $value) ?? $value;

            return trim($value);
        }

        $value = trim(strip_tags($value));

        if (in_array($type, ['image', 'link'], true)) {
            $value = preg_replace('/[\x00-\x1F\x7F]/u', '', $value) ?? $value;

            if (preg_match('/^\s*(javascript|data):/i', $value)) {
                return '#';
            }
        }

        if ($type === 'number') {
            $value = preg_replace('/[^0-9.,+\-\s]/', '', $value) ?? $value;
        }

        return trim($value);
    }

    public function cleanMixed(mixed $value): mixed
    {
        if (is_array($value)) {
            return collect($value)
                ->map(fn ($item) => $this->cleanMixed($item))
                ->all();
        }

        return is_string($value) ? $this->clean($value) : $value;
    }
}
