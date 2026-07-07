<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\ContentRepository;
use App\Services\ImageVariantService;
use App\Support\ContentLocale;
use Inertia\Inertia;
use Inertia\Response;

class RavenalaProjectController extends Controller
{
    public function __invoke(ContentRepository $contentRepository, ImageVariantService $images): Response
    {
        $locale = ContentLocale::fromRequest();
        $blocks = $contentRepository->blocks($locale);
        $blocks['popup'] = $contentRepository->visitorPopup($blocks['popup'] ?? [], $locale);
        $canEdit = (bool) request()->user()?->is_admin;

        return Inertia::render('Projets/Ravenala', [
            'locale' => $locale,
            'languageUrls' => [
                'fr' => route('projects.ravenala'),
                'en' => route('home.en'),
            ],
            'blocks' => $blocks,
            'collections' => $contentRepository->collections($locale),
            'visitorPopups' => $canEdit ? $contentRepository->visitorPopups($locale) : [],
            'performance' => [
                'siteLogo' => $images->forUrl(
                    $blocks['site']['logo'] ?? null,
                    ImageVariantService::LOGO_WIDTHS,
                    ImageVariantService::LOGO_SIZES,
                ),
            ],
            'canEdit' => $canEdit,
        ]);
    }
}
