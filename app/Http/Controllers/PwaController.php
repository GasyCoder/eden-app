<?php

namespace App\Http\Controllers;

use App\Models\ContentBlock;
use App\Support\ContentLocale;
use App\Support\SeoSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class PwaController extends Controller
{
    /**
     * Manifeste de l'application installable, construit à partir des Paramètres du site
     * (nom, description, logo) : aucune modification de fichier n'est nécessaire.
     */
    public function manifest(): JsonResponse
    {
        $blocks = ContentBlock::query()->whereIn('key', ['site.name', 'site.caption', 'site.logo', 'site.favicon'])
            ->where('locale', ContentLocale::DEFAULT)->pluck('value', 'key');
        $name = ($blocks['site.name'] ?? null) ?: 'EDEN';
        $version = substr(md5(($blocks['site.logo'] ?? '').($blocks['site.favicon'] ?? '')), 0, 8);
        $icon = fn (string $spec, string $size, string $purpose) => ['src' => "/pwa/icon-{$spec}.png?v={$version}", 'sizes' => $size, 'type' => 'image/png', 'purpose' => $purpose];
        $shortcut = fn (string $name, string $url, string $description) => [
            'name' => $name, 'short_name' => $name, 'url' => $url, 'description' => $description,
            'icons' => [['src' => "/pwa/icon-192.png?v={$version}", 'sizes' => '192x192', 'type' => 'image/png']],
        ];

        return response()->json([
            'id' => '/',
            'name' => trim($name.' · '.(($blocks['site.caption'] ?? null) ?: 'Université de Mahajanga')),
            'short_name' => $name,
            'description' => SeoSettings::all()['description'],
            'lang' => 'fr',
            'dir' => 'ltr',
            'start_url' => '/?source=pwa',
            'scope' => '/',
            'display' => 'standalone',
            'display_override' => ['standalone', 'minimal-ui'],
            'orientation' => 'any',
            'background_color' => '#FFFFFF',
            'theme_color' => '#13692C',
            'categories' => ['education', 'science', 'news'],
            'icons' => [
                $icon('192', '192x192', 'any'),
                $icon('512', '512x512', 'any'),
                $icon('maskable-512', '512x512', 'maskable'),
            ],
            'shortcuts' => [
                $shortcut('Actualités', '/actualites?source=pwa', 'Les dernières nouvelles de l’EDEN'),
                $shortcut('Événements', '/evenements?source=pwa', 'Soutenances, séminaires et ateliers'),
                $shortcut('Projets', '/projets?source=pwa', 'Projets de recherche et de coopération'),
                $shortcut('Revues', '/revues?source=pwa', 'Le journal MaToY et les revues'),
            ],
        ], 200, ['Content-Type' => 'application/manifest+json', 'Cache-Control' => 'public, max-age=3600'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Génère une icône PWA carrée à partir du logo (ou du favicon) téléversé.
     * Repli sur le monogramme par défaut si aucune image personnalisée n'est disponible.
     *
     * spec : "192", "512", "180" ou "maskable-512"
     */
    public function icon(Request $request, string $spec): Response
    {
        $maskable = str_contains($spec, 'maskable');
        $size = (int) preg_replace('/\D/', '', $spec) ?: 192;
        $size = max(48, min($size, 1024));

        $sourcePath = $this->sourcePath();

        // Pas d'image perso (ou GD indisponible) → on renvoie le monogramme par défaut
        if (! $sourcePath || ! function_exists('imagecreatefromstring')) {
            return $this->fallback($size, $maskable);
        }

        $cacheRel = 'pwa/'.md5($sourcePath.'|'.@filemtime($sourcePath).'|'.$size.'|'.($maskable ? 'm' : 'n')).'.png';
        $disk = Storage::disk('public');

        if (! $disk->exists($cacheRel)) {
            $png = $this->render($sourcePath, $size, $maskable);

            if ($png === null) {
                return $this->fallback($size, $maskable);
            }

            $disk->put($cacheRel, $png);
        }

        return response()->file($disk->path($cacheRel), [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    private function sourcePath(): ?string
    {
        $url = ContentBlock::where('key', 'site.logo')->where('locale', ContentLocale::DEFAULT)->value('value')
            ?: ContentBlock::where('key', 'site.favicon')->where('locale', ContentLocale::DEFAULT)->value('value');

        if (! $url) {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH) ?: $url;

        if (! str_starts_with($path, '/storage/') || str_contains($path, '..')) {
            return null;
        }

        $relative = ltrim(substr($path, strlen('/storage/')), '/');
        $full = Storage::disk('public')->path($relative);

        return is_file($full) ? $full : null;
    }

    private function render(string $sourcePath, int $size, bool $maskable): ?string
    {
        $bytes = @file_get_contents($sourcePath);

        if ($bytes === false) {
            return null;
        }

        $source = @imagecreatefromstring($bytes);

        if (! $source) {
            return null;
        }

        $sw = imagesx($source);
        $sh = imagesy($source);

        $canvas = imagecreatetruecolor($size, $size);
        imagefilledrectangle($canvas, 0, 0, $size, $size, imagecolorallocate($canvas, 255, 255, 255));

        // Zone occupée par le logo : plus petite pour les icônes "maskable" (zone de sécurité)
        $maxDim = (int) round($size * ($maskable ? 0.66 : 0.86));
        $ratio = min($maxDim / $sw, $maxDim / $sh);
        $dw = max(1, (int) round($sw * $ratio));
        $dh = max(1, (int) round($sh * $ratio));
        $dx = (int) round(($size - $dw) / 2);
        $dy = (int) round(($size - $dh) / 2);

        imagealphablending($canvas, true);
        imagecopyresampled($canvas, $source, $dx, $dy, 0, 0, $dw, $dh, $sw, $sh);

        ob_start();
        imagepng($canvas);
        $png = ob_get_clean();

        imagedestroy($source);
        imagedestroy($canvas);

        return $png ?: null;
    }

    private function fallback(int $size, bool $maskable): Response
    {
        $file = match (true) {
            $maskable => 'icons/icon-maskable-512.png',
            $size >= 512 => 'icons/icon-512.png',
            $size <= 180 && $size > 64 => 'icons/apple-touch-icon.png',
            default => 'icons/icon-192.png',
        };

        return response()->file(public_path($file), [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
