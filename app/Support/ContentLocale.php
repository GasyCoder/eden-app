<?php

namespace App\Support;

use Illuminate\Http\Request;

class ContentLocale
{
    public const DEFAULT = 'fr';

    public const SUPPORTED = ['fr', 'en'];

    public static function normalize(?string $locale): string
    {
        $locale = strtolower((string) $locale);

        return in_array($locale, self::SUPPORTED, true) ? $locale : self::DEFAULT;
    }

    public static function fromRequest(?Request $request = null): string
    {
        $request ??= request();

        $locale = $request->input('locale')
            ?: $request->route('locale')
            ?: self::fromReferer($request)
            ?: self::fromPath($request->path());

        return self::normalize($locale);
    }

    private static function fromReferer(Request $request): ?string
    {
        $referer = $request->headers->get('referer');

        if (! $referer) {
            return null;
        }

        $path = parse_url($referer, PHP_URL_PATH);

        return is_string($path) ? self::fromPath(ltrim($path, '/')) : null;
    }

    private static function fromPath(string $path): ?string
    {
        $segment = explode('/', trim($path, '/'))[0] ?? null;

        return in_array($segment, self::SUPPORTED, true) ? $segment : null;
    }
}
