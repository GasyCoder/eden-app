<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ImageVariantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageUploadController extends Controller
{
    public function store(Request $request, ImageVariantService $images): JsonResponse
    {
        $validated = $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
            'content_key' => ['nullable', 'string', 'max:120'],
        ]);

        $path = $validated['image']->store('content', 'public');
        $url = Storage::url($path);
        [$widths, $sizes] = $this->variantPreset($validated['content_key'] ?? null);
        $variants = $images->forUrl($url, $widths, $sizes);

        return response()->json([
            'path' => $url,
            'variants' => $variants,
        ]);
    }

    /**
     * @return array{0:array<int,int>,1:string}
     */
    private function variantPreset(?string $contentKey): array
    {
        return match ($contentKey) {
            'hero.image' => [ImageVariantService::HERO_WIDTHS, ImageVariantService::HERO_SIZES],
            'site.logo' => [ImageVariantService::LOGO_WIDTHS, ImageVariantService::LOGO_SIZES],
            'contact.map_image' => [ImageVariantService::CONTACT_WIDTHS, ImageVariantService::CONTACT_SIZES],
            default => [ImageVariantService::DEFAULT_WIDTHS, ImageVariantService::DEFAULT_SIZES],
        };
    }
}
