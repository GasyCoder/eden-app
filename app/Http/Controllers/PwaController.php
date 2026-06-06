<?php

namespace App\Http\Controllers;

use App\Models\ContentBlock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class PwaController extends Controller
{
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
        $url = ContentBlock::where('key', 'site.logo')->value('value')
            ?: ContentBlock::where('key', 'site.favicon')->value('value');

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
