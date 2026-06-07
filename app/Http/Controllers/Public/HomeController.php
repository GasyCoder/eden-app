<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\ContentRepository;
use App\Services\ImageVariantService;
use App\Support\ContentLocale;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(ContentRepository $contentRepository, ImageVariantService $images): Response
    {
        $locale = ContentLocale::fromRequest();
        $blocks = $contentRepository->blocks($locale);
        $blocks['popup'] = $contentRepository->visitorPopup($blocks['popup'] ?? [], $locale);
        $canEdit = (bool) request()->user()?->is_admin;

        return Inertia::render('Home', [
            'locale' => $locale,
            'locales' => ContentLocale::SUPPORTED,
            'languageUrls' => [
                'fr' => route('home'),
                'en' => route('home.en'),
            ],
            'blocks' => $blocks,
            'blockMeta' => $contentRepository->blockMeta($locale),
            'collections' => $contentRepository->collections($locale),
            'visitorPopups' => $canEdit ? $contentRepository->visitorPopups($locale) : [],
            'performance' => [
                'heroImage' => $images->forUrl(
                    $blocks['hero']['image'] ?? null,
                    ImageVariantService::HERO_WIDTHS,
                    ImageVariantService::HERO_SIZES,
                ),
                'siteLogo' => $images->forUrl(
                    $blocks['site']['logo'] ?? null,
                    ImageVariantService::LOGO_WIDTHS,
                    ImageVariantService::LOGO_SIZES,
                ),
                'contactMapImage' => $images->forUrl(
                    $blocks['contact']['map_image'] ?? null,
                    ImageVariantService::CONTACT_WIDTHS,
                    ImageVariantService::CONTACT_SIZES,
                ),
            ],
            'canEdit' => $canEdit,
        ]);
    }
}
