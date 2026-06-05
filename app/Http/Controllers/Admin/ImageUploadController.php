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
        ]);

        $path = $validated['image']->store('content', 'public');
        $url = Storage::url($path);
        $variants = $images->forUrl($url);

        return response()->json([
            'path' => $url,
            'variants' => $variants,
        ]);
    }
}
