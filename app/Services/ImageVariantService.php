<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageVariantService
{
    public const HERO_WIDTHS = [480, 768, 1024, 1440];
    public const DEFAULT_WIDTHS = [480, 768, 1024];

    public const HERO_SIZES = '(max-width: 768px) calc(100vw - 2.5rem), (max-width: 1180px) 45vw, 560px';
    public const DEFAULT_SIZES = '(max-width: 768px) calc(100vw - 2.5rem), 420px';

    /**
     * @return array{
     *     src:string|null,
     *     width:int|null,
     *     height:int|null,
     *     sizes:string,
     *     sources:array<int,array{type:string,srcset:string}>,
     *     preload:array{href:string,type:string,srcset:string,sizes:string}|null
     * }
     */
    public function forUrl(?string $url, array $widths = self::DEFAULT_WIDTHS, string $sizes = self::DEFAULT_SIZES): array
    {
        $result = [
            'src' => $url,
            'width' => null,
            'height' => null,
            'sizes' => $sizes,
            'sources' => [],
            'preload' => null,
        ];

        if (!$url || !function_exists('imagewebp')) {
            return $result;
        }

        $relativePath = $this->storageRelativePath($url);

        if (!$relativePath) {
            return $result;
        }

        $sourcePath = Storage::disk('public')->path($relativePath);

        if (!is_file($sourcePath)) {
            return $result;
        }

        $info = @getimagesize($sourcePath);

        if (!$info || empty($info[0]) || empty($info[1]) || ($info['mime'] ?? '') === 'image/gif') {
            return $result;
        }

        $result['width'] = (int) $info[0];
        $result['height'] = (int) $info[1];

        $targetWidths = collect($widths)
            ->map(fn ($width) => (int) $width)
            ->filter(fn ($width) => $width > 0 && $width < (int) $info[0])
            ->push((int) $info[0])
            ->unique()
            ->sort()
            ->values()
            ->all();

        foreach ($this->formats() as $format => $mime) {
            $variants = [];

            foreach ($targetWidths as $width) {
                $variant = $this->variant($sourcePath, $relativePath, (int) $info[0], (int) $info[1], $width, $format);

                if ($variant) {
                    $variants[] = $variant['url'].' '.$variant['width'].'w';
                }
            }

            if ($variants !== []) {
                $srcset = implode(', ', $variants);

                $result['sources'][] = [
                    'type' => $mime,
                    'srcset' => $srcset,
                ];

                $result['preload'] ??= [
                    'href' => Str::before($variants[0], ' '),
                    'type' => $mime,
                    'srcset' => $srcset,
                    'sizes' => $sizes,
                ];
            }
        }

        return $result;
    }

    public function generateForUrls(iterable $urls): int
    {
        $count = 0;

        foreach ($urls as $url) {
            $before = $this->variantCount($url);
            $this->forUrl($url);
            $count += max(0, $this->variantCount($url) - $before);
        }

        return $count;
    }

    private function variant(string $sourcePath, string $relativePath, int $sourceWidth, int $sourceHeight, int $width, string $format): ?array
    {
        $targetRelativePath = $this->variantRelativePath($relativePath, $width, $format);
        $targetPath = Storage::disk('public')->path($targetRelativePath);

        if (!is_file($targetPath)) {
            Storage::disk('public')->makeDirectory(dirname($targetRelativePath));

            if (!$this->resize($sourcePath, $targetPath, $sourceWidth, $sourceHeight, $width, $format)) {
                return null;
            }
        }

        return [
            'url' => '/storage/'.ltrim($targetRelativePath, '/'),
            'width' => $width,
        ];
    }

    private function resize(string $sourcePath, string $targetPath, int $sourceWidth, int $sourceHeight, int $targetWidth, string $format): bool
    {
        $bytes = @file_get_contents($sourcePath);

        if ($bytes === false) {
            return false;
        }

        $source = @imagecreatefromstring($bytes);

        if (!$source) {
            return false;
        }

        $targetHeight = max(1, (int) round($sourceHeight * ($targetWidth / $sourceWidth)));
        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);

        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagefilledrectangle($canvas, 0, 0, $targetWidth, $targetHeight, imagecolorallocatealpha($canvas, 0, 0, 0, 127));

        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $sourceWidth, $sourceHeight);

        $saved = match ($format) {
            'avif' => function_exists('imageavif') && @imageavif($canvas, $targetPath, 48, 6),
            'webp' => @imagewebp($canvas, $targetPath, 78),
            default => false,
        };

        imagedestroy($source);
        imagedestroy($canvas);

        if (!$saved) {
            @unlink($targetPath);
        }

        return (bool) $saved;
    }

    /**
     * @return array<string,string>
     */
    private function formats(): array
    {
        return array_filter([
            'avif' => function_exists('imageavif') ? 'image/avif' : null,
            'webp' => function_exists('imagewebp') ? 'image/webp' : null,
        ]);
    }

    private function storageRelativePath(string $url): ?string
    {
        $path = parse_url($url, PHP_URL_PATH) ?: $url;

        if (!str_starts_with($path, '/storage/')) {
            return null;
        }

        $relativePath = ltrim(Str::after($path, '/storage/'), '/');

        return str_contains($relativePath, '..') ? null : $relativePath;
    }

    private function variantRelativePath(string $relativePath, int $width, string $format): string
    {
        $info = pathinfo($relativePath);
        $directory = ($info['dirname'] ?? '.') === '.' ? '' : trim($info['dirname'], '/').'/';

        return $directory.'optimized/'.$info['filename'].'-'.$width.'.'.$format;
    }

    private function variantCount(?string $url): int
    {
        $relativePath = $url ? $this->storageRelativePath($url) : null;

        if (!$relativePath) {
            return 0;
        }

        $info = pathinfo($relativePath);
        $directory = (($info['dirname'] ?? '.') === '.' ? '' : trim($info['dirname'], '/').'/').'optimized';
        $prefix = $info['filename'].'-';

        return collect(Storage::disk('public')->files($directory))
            ->filter(fn ($path) => str_starts_with(pathinfo($path, PATHINFO_BASENAME), $prefix))
            ->count();
    }
}
