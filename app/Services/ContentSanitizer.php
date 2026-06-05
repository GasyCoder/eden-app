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

        if ($type === 'html') {
            $value = strip_tags($value, '<p><br><strong><em><b><i><ul><ol><li><a>');
            $value = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $value) ?? $value;
            $value = preg_replace('/javascript\s*:/i', '', $value) ?? $value;

            return trim($value);
        }

        return trim(strip_tags($value));
    }
}
