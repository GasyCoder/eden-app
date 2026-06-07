<?php

use App\Models\ContentBlock;
use App\Models\ContentItem;
use App\Services\ImageVariantService;
use App\Support\ContentLocale;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('content:generate-image-variants', function () {
    $images = app(ImageVariantService::class);
    $before = count(Storage::disk('public')->allFiles('content/optimized'));
    $heroImage = ContentBlock::query()->where('key', 'hero.image')->where('locale', ContentLocale::DEFAULT)->value('value');

    if ($heroImage) {
        $images->forUrl($heroImage, ImageVariantService::HERO_WIDTHS, ImageVariantService::HERO_SIZES);
    }

    $siteLogo = ContentBlock::query()->where('key', 'site.logo')->where('locale', ContentLocale::DEFAULT)->value('value');

    if ($siteLogo) {
        $images->forUrl($siteLogo, ImageVariantService::LOGO_WIDTHS, ImageVariantService::LOGO_SIZES);
    }

    $contactMapImage = ContentBlock::query()->where('key', 'contact.map_image')->where('locale', ContentLocale::DEFAULT)->value('value');

    if ($contactMapImage) {
        $images->forUrl($contactMapImage, ImageVariantService::CONTACT_WIDTHS, ImageVariantService::CONTACT_SIZES);
    }

    $urls = ContentBlock::query()
        ->where('type', 'image')
        ->whereNotNull('value')
        ->pluck('value')
        ->merge(ContentItem::query()->whereNotNull('image')->pluck('image'))
        ->filter()
        ->unique()
        ->values();

    $images->generateForUrls($urls);
    $count = max(0, count(Storage::disk('public')->allFiles('content/optimized')) - $before);

    $this->info("Variantes d'images vérifiées pour {$urls->count()} image(s).");
    $this->info("Nouvelles variantes générées : {$count}.");
})->purpose('Generate responsive AVIF/WebP variants for dynamic content images');
