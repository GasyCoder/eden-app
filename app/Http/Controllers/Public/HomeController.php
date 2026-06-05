<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\ImageVariantService;
use App\Services\ContentRepository;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(ContentRepository $contentRepository, ImageVariantService $images): Response
    {
        $blocks = $contentRepository->blocks();

        return Inertia::render('Home', [
            'blocks' => $blocks,
            'collections' => $contentRepository->collections(),
            'performance' => [
                'heroImage' => $images->forUrl(
                    $blocks['hero']['image'] ?? null,
                    ImageVariantService::HERO_WIDTHS,
                    ImageVariantService::HERO_SIZES,
                ),
            ],
            'canEdit' => (bool) request()->user()?->is_admin,
        ]);
    }
}
