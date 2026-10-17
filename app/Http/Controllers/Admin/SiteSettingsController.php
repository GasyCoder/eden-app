<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentBlock;
use App\Models\ContentItem;
use App\Models\User;
use App\Services\ContentRevisionRecorder;
use App\Services\ContentSanitizer;
use App\Services\ImageVariantService;
use App\Support\ContentLocale;
use App\Support\SeoSettings;
use App\Support\ThemeSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Paramètres du site (superadmin) : identité, référencement, contact,
 * réseaux sociaux et couleurs des sections.
 */
class SiteSettingsController extends Controller
{
    /** Champ du formulaire => [clé du bloc, type, groupe]. */
    private const FIELDS = [
        'site_name' => ['site.name', 'text', 'site'],
        'site_caption' => ['site.caption', 'text', 'site'],
        'logo' => ['site.logo', 'image', 'site'],
        'favicon' => ['site.favicon', 'image', 'seo'],
        'seo_title' => ['seo.title', 'text', 'seo'],
        'seo_description' => ['seo.description', 'textarea', 'seo'],
        'seo_keywords' => ['seo.keywords', 'text', 'seo'],
        'og_image' => ['seo.og_image', 'image', 'seo'],
        'robots' => ['seo.robots', 'text', 'seo'],
        'google_verification' => ['seo.google_verification', 'text', 'seo'],
        'analytics_id' => ['seo.analytics_id', 'text', 'seo'],
        'contact_email' => ['contact.email', 'text', 'contact'],
        'contact_phone' => ['contact.phone', 'text', 'contact'],
        'contact_address' => ['contact.address', 'textarea', 'contact'],
        'contact_website' => ['contact.website', 'text', 'contact'],
    ];

    /** Réseaux proposés (le pied de page choisit l'icône d'après le nom). */
    public const NETWORKS = ['Facebook', 'LinkedIn', 'X', 'YouTube', 'Instagram', 'TikTok', 'WhatsApp', 'ResearchGate', 'Google Scholar'];

    public function edit(Request $request): Response
    {
        $this->authorizeSuperAdmin($request);

        $blocks = ContentBlock::query()
            ->whereIn('key', array_column(self::FIELDS, 0))
            ->where('locale', ContentLocale::DEFAULT)
            ->pluck('value', 'key');

        $settings = collect(self::FIELDS)->map(fn (array $field) => (string) ($blocks[$field[0]] ?? ''))->all();
        $settings['robots'] = $settings['robots'] === 'noindex' ? 'noindex' : 'index';

        return Inertia::render('Admin/Settings/Edit', [
            'settings' => $settings,
            'socials' => ContentItem::query()->where('collection_key', 'footer_socials')->where('locale', ContentLocale::DEFAULT)
                ->orderBy('position')->orderBy('id')->get()
                ->map(fn (ContentItem $item) => ['title' => collect(self::NETWORKS)->first(fn ($name) => strcasecmp($name, (string) $item->title) === 0) ?? ($item->title ?? ''), 'link_url' => $item->link_url === '#' ? '' : ($item->link_url ?? '')])
                ->values()->all(),
            'networks' => self::NETWORKS,
            'theme' => ['sections' => ThemeSettings::SECTIONS, 'colors' => ThemeSettings::all()],
            'defaults' => SeoSettings::DEFAULTS,
            'editors' => User::query()->where('role', 'editor')->orderBy('name')->get(['id', 'name', 'email', 'created_at'])
                ->map(fn (User $editor) => [
                    'id' => $editor->id,
                    'name' => $editor->name,
                    'email' => $editor->email,
                    'created_at' => $editor->created_at?->format('d/m/Y'),
                ])->values()->all(),
        ]);
    }

    public function update(Request $request, ContentSanitizer $sanitizer, ContentRevisionRecorder $revisions, ImageVariantService $images): RedirectResponse
    {
        $this->authorizeSuperAdmin($request);

        $data = $request->validate([
            'site_name' => ['nullable', 'string', 'max:80'],
            'site_caption' => ['nullable', 'string', 'max:160'],
            'logo' => ['nullable', 'string', 'max:2048'],
            'favicon' => ['nullable', 'string', 'max:2048'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:600'],
            'seo_keywords' => ['nullable', 'string', 'max:600'],
            'og_image' => ['nullable', 'string', 'max:2048'],
            'robots' => ['required', 'in:index,noindex'],
            'google_verification' => ['nullable', 'string', 'max:120', 'regex:/^[A-Za-z0-9_\-]+$/'],
            'analytics_id' => ['nullable', 'string', 'regex:/^G-[A-Z0-9]{4,16}$/'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:60'],
            'contact_address' => ['nullable', 'string', 'max:500'],
            'contact_website' => ['nullable', 'string', 'max:255'],
            'socials' => ['nullable', 'array', 'max:12'],
            'socials.*.title' => ['required', 'string', 'max:60'],
            'socials.*.link_url' => ['required', 'url:http,https', 'max:2048'],
            'colors' => ['nullable', 'array'],
            'colors.*' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ], [
            'google_verification.regex' => 'Copiez uniquement la valeur « content » de la balise fournie par Google.',
            'analytics_id.regex' => 'Identifiant attendu au format G-XXXXXXXXXX.',
            'socials.*.title.required' => 'Choisissez le réseau.',
            'socials.*.link_url.required' => 'Indiquez l’adresse de la page.',
            'socials.*.link_url.url' => 'Adresse complète attendue (https://…).',
        ]);

        foreach (self::FIELDS as $field => [$key, $type, $group]) {
            $value = $sanitizer->clean($data[$field] ?? null, in_array($type, ['image', 'textarea'], true) ? ($type === 'image' ? 'link' : 'textarea') : 'text');
            $this->setBlock($key, $value === '' ? null : $value, $type, $group, $revisions);
        }

        foreach (['logo' => [ImageVariantService::LOGO_WIDTHS, ImageVariantService::LOGO_SIZES], 'og_image' => [ImageVariantService::DEFAULT_WIDTHS, ImageVariantService::DEFAULT_SIZES]] as $field => [$widths, $sizes]) {
            if (filled($data[$field] ?? null)) {
                $images->forUrl($data[$field], $widths, $sizes);
            }
        }

        $this->syncSocials($data['socials'] ?? [], $sanitizer);

        foreach (array_keys(ThemeSettings::SECTIONS) as $section) {
            $this->setBlock(ThemeSettings::key($section), ($data['colors'][$section] ?? null) ?: null, 'color', 'theme', $revisions);
        }

        return back(303)->with('success', 'Paramètres du site enregistrés.');
    }

    private function authorizeSuperAdmin(Request $request): void
    {
        abort_unless($request->user()?->isSuperAdmin(), 403, 'Réservé au superadmin.');
    }

    private function setBlock(string $key, ?string $value, string $type, string $group, ContentRevisionRecorder $revisions): void
    {
        $block = ContentBlock::firstOrNew(['key' => $key, 'locale' => ContentLocale::DEFAULT]);

        if ((string) $block->value === (string) $value) {
            return;
        }

        $before = $block->exists ? $revisions->snapshotBlock($block) : null;
        $wasExisting = $block->exists;
        $block->fill(['type' => $type, 'group' => $block->group ?? $group, 'value' => $value, 'is_active' => true])->save();

        $revisions->recordBlock($block, $wasExisting ? 'updated' : 'created', $before, $revisions->snapshotBlock($block));
    }

    /** Les liens sociaux sont identiques en français et en anglais. */
    private function syncSocials(array $socials, ContentSanitizer $sanitizer): void
    {
        foreach (ContentLocale::SUPPORTED as $locale) {
            ContentItem::query()->where('collection_key', 'footer_socials')->where('locale', $locale)->delete();

            foreach (array_values($socials) as $position => $social) {
                ContentItem::create([
                    'collection_key' => 'footer_socials',
                    'locale' => $locale,
                    'title' => $sanitizer->clean($social['title']),
                    'link_url' => $sanitizer->clean($social['link_url'], 'link'),
                    'position' => $position + 1,
                    'is_active' => true,
                ]);
            }
        }
    }
}
