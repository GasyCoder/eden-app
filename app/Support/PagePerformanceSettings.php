<?php

namespace App\Support;

use App\Models\ContentBlock;
use App\Services\ImageVariantService;

class PagePerformanceSettings
{
    /**
     * @return array{heroImage:array}
     */
    public static function all(): array
    {
        try {
            $heroImage = ContentBlock::where('key', 'hero.image')->value('value');
        } catch (\Throwable) {
            $heroImage = null;
        }

        return [
            'heroImage' => app(ImageVariantService::class)->forUrl(
                $heroImage,
                ImageVariantService::HERO_WIDTHS,
                ImageVariantService::HERO_SIZES,
            ),
        ];
    }
}
