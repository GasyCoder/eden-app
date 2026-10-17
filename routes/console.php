<?php

use App\Models\ContentBlock;
use App\Models\ContentItem;
use App\Services\ContentRevisionRecorder;
use App\Services\ImageVariantService;
use App\Services\Newsletter\NewsletterDispatcher;
use App\Support\ContentLocale;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

/*
 * Remplace les tirets longs (—) et demi-cadratins (–) dans les contenus éditables.
 * Simulation par défaut ; --apply enregistre les changements (tracés dans l'historique,
 * donc restaurables depuis la barre d'administration).
 */
Artisan::command('content:normalize-dashes {--apply : Enregistre les modifications}', function () {
    $recorder = app(ContentRevisionRecorder::class);
    $normalize = function (?string $value): ?string {
        if ($value === null || ! preg_match('/[—–]/u', $value)) {
            return $value;
        }

        $value = preg_replace('/(EAD \d) — /u', '$1 : ', $value);
        $value = str_replace(['EDEN-UMG — ', ' — ', ' – '], ['EDEN-UMG | ', ', ', ' - '], $value);

        return str_replace(['—', '–'], '-', $value);
    };

    $apply = (bool) $this->option('apply');
    $count = 0;

    ContentBlock::query()->whereNotNull('value')->get()->each(function (ContentBlock $block) use ($normalize, $apply, $recorder, &$count): void {
        $next = $normalize($block->value);

        if ($next === $block->value) {
            return;
        }

        $count++;
        $this->line("bloc {$block->key} ({$block->locale}) : ".Str::limit($next, 90));

        if ($apply) {
            $before = $recorder->snapshotBlock($block);
            $block->update(['value' => $next]);
            $recorder->recordBlock($block, 'updated', $before, $recorder->snapshotBlock($block));
        }
    });

    ContentItem::query()->get()->each(function (ContentItem $item) use ($normalize, $apply, $recorder, &$count): void {
        $changes = [];

        foreach (['title', 'subtitle', 'description', 'link_label'] as $field) {
            $next = $normalize($item->{$field});

            if ($next !== $item->{$field}) {
                $changes[$field] = $next;
            }
        }

        $meta = $item->meta ?? [];
        $nextMeta = collect($meta)->map(fn ($value) => is_string($value) ? $normalize($value) : $value)->all();

        if ($nextMeta !== $meta) {
            $changes['meta'] = $nextMeta;
        }

        if (! $changes) {
            return;
        }

        $count++;
        $this->line("élément {$item->collection_key} #{$item->id} : ".implode(', ', array_keys($changes)));

        if ($apply) {
            $before = $recorder->snapshotItem($item);
            $item->update($changes);
            $recorder->recordItem($item, 'updated', $before, $recorder->snapshotItem($item));
        }
    });

    $this->info($apply ? "{$count} contenu(s) mis à jour." : "{$count} contenu(s) à mettre à jour. Relancez avec --apply pour enregistrer.");
})->purpose('Remplace les tirets longs dans les contenus éditables (simulation par défaut)');

/*
 * Newsletter : lance les envois dont le contenu est devenu public (publication immédiate
 * ou programmée). Le planificateur doit être appelé chaque minute par cron :
 *   * * * * * cd /chemin/vers/eden-app && php artisan schedule:run >> /dev/null 2>&1
 */
Artisan::command('newsletter:dispatch', function (NewsletterDispatcher $dispatcher) {
    $started = $dispatcher->dispatchDue();
    $this->info($started ? "{$started} envoi(s) lancé(s)." : 'Aucun envoi à lancer.');
})->purpose('Lance les envois newsletter programmés dont le contenu est publié');

Schedule::command('newsletter:dispatch')->everyMinute()->withoutOverlapping();

// Hébergement sans superviseur : la file d'attente est traitée par le planificateur.
// Avec Supervisor (php artisan queue:work en continu), passer EDEN_QUEUE_VIA_SCHEDULER=false.
if (config('eden.queue_via_scheduler')) {
    Schedule::command('queue:work --stop-when-empty --max-time=55 --tries=3 --sleep=1')
        ->everyMinute()
        ->withoutOverlapping()
        ->runInBackground();
}
