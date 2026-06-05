<?php

namespace App\Http\Controllers;

use App\Models\ContentBlock;
use App\Services\ContentSanitizer;
use App\Services\ImageVariantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function update(Request $request, ContentSanitizer $sanitizer, ImageVariantService $images): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403, 'Réservé au superadmin.');

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:600'],
            'keywords' => ['nullable', 'string', 'max:600'],
            'favicon' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:1024'],
            'og_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
        ]);

        $this->setBlock('seo.title', $sanitizer->clean($validated['title'] ?? null), 'text');
        $this->setBlock('seo.description', $sanitizer->clean($validated['description'] ?? null, 'textarea'), 'textarea');
        $this->setBlock('seo.keywords', $sanitizer->clean($validated['keywords'] ?? null), 'text');

        if ($request->hasFile('favicon')) {
            $path = $request->file('favicon')->store('content', 'public');
            $url = Storage::url($path);
            $images->forUrl($url);
            $this->setBlock('site.favicon', $url, 'image');
        }

        if ($request->hasFile('og_image')) {
            $path = $request->file('og_image')->store('content', 'public');
            $url = Storage::url($path);
            $images->forUrl($url);
            $this->setBlock('seo.og_image', $url, 'image');
        }

        return back(303)->with('success', 'Référencement mis à jour.');
    }

    private function setBlock(string $key, ?string $value, string $type): void
    {
        ContentBlock::updateOrCreate(
            ['key' => $key],
            [
                'type' => $type,
                'value' => $value,
                'group' => 'seo',
                'is_active' => true,
            ],
        );
    }
}
