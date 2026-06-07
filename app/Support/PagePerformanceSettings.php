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
            $heroImage = ContentBlock::where('key', 'hero.image')
                ->where('locale', ContentLocale::fromRequest())
                ->value('value')
                ?: ContentBlock::where('key', 'hero.image')
                    ->where('locale', ContentLocale::DEFAULT)
                    ->value('value');
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
