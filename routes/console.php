<?php

use App\Models\ContentBlock;
use App\Models\ContentItem;
use App\Services\ImageVariantService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('content:generate-image-variants', function () {
    $images = app(ImageVariantService::class);
    $heroImage = ContentBlock::query()->where('key', 'hero.image')->value('value');

    if ($heroImage) {
        $images->forUrl($heroImage, ImageVariantService::HERO_WIDTHS, ImageVariantService::HERO_SIZES);
    }

    $urls = ContentBlock::query()
        ->where('type', 'image')
        ->whereNotNull('value')
        ->pluck('value')
        ->merge(ContentItem::query()->whereNotNull('image')->pluck('image'))
        ->filter()
        ->unique()
        ->values();

    $count = $images->generateForUrls($urls);

    $this->info("Variantes d'images vérifiées pour {$urls->count()} image(s).");
    $this->info("Nouvelles variantes générées : {$count}.");
})->purpose('Generate responsive AVIF/WebP variants for dynamic content images');
