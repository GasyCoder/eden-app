<?php

namespace App\Http\Controllers;

use App\Models\ContentBlock;
use App\Services\ContentSanitizer;
use App\Services\ImageVariantService;
use App\Support\ContentLocale;
use App\Support\ThemeSettings;
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
            'colors' => ['nullable', 'array'],
            'colors.*' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        // On ne met à jour que les champs réellement envoyés (deux formulaires partagent cet endpoint)
        if ($request->has('title')) {
            $this->setBlock('seo.title', $sanitizer->clean($validated['title'] ?? null), 'text');
        }

        if ($request->has('description')) {
            $this->setBlock('seo.description', $sanitizer->clean($validated['description'] ?? null, 'textarea'), 'textarea');
        }

        if ($request->has('keywords')) {
            $this->setBlock('seo.keywords', $sanitizer->clean($validated['keywords'] ?? null), 'text');
        }

        if ($request->hasFile('favicon')) {
            $url = Storage::url($request->file('favicon')->store('content', 'public'));
            $images->forUrl($url);
            $this->setBlock('site.favicon', $url, 'image');
        }

        if ($request->hasFile('og_image')) {
            $url = Storage::url($request->file('og_image')->store('content', 'public'));
            $images->forUrl($url);
            $this->setBlock('seo.og_image', $url, 'image');
        }

        if ($request->has('colors')) {
            foreach (array_keys(ThemeSettings::SECTIONS) as $section) {
                $color = $validated['colors'][$section] ?? null;
                $this->setBlock(ThemeSettings::key($section), $color ?: null, 'color', 'theme');
            }
        }

        return back(303)->with('success', 'Réglages mis à jour.');
    }

    private function setBlock(string $key, ?string $value, string $type, string $group = 'seo'): void
    {
        ContentBlock::updateOrCreate(
            [
                'key' => $key,
                'locale' => ContentLocale::DEFAULT,
            ],
            [
                'type' => $type,
                'value' => $value,
                'group' => $group,
                'is_active' => true,
            ],
        );
    }
}
