<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\ContentRepository;
use App\Services\ImageVariantService;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(ContentRepository $contentRepository, ImageVariantService $images): Response
    {
        $blocks = $contentRepository->blocks();
        $blocks['popup'] = $contentRepository->visitorPopup($blocks['popup'] ?? []);
        $canEdit = (bool) request()->user()?->is_admin;

        return Inertia::render('Home', [
            'blocks' => $blocks,
            'blockMeta' => $contentRepository->blockMeta(),
            'collections' => $contentRepository->collections(),
            'visitorPopups' => $canEdit ? $contentRepository->visitorPopups() : [],
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
