<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\PublicPageProps;
use App\Support\ContentLocale;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Pages institutionnelles composées des sections éditables existantes
 * (mêmes clés de contenu que l'ancienne page d'accueil).
 */
class InstitutionalPageController extends Controller
{
    public function about(PublicPageProps $pages): Response
    {
        return $this->render('Institution/About', 'about', $pages);
    }

    public function training(PublicPageProps $pages): Response
    {
        return $this->render('Institution/Training', 'training', $pages);
    }

    public function research(PublicPageProps $pages): Response
    {
        $locale = ContentLocale::fromRequest();

        return $this->render('Institution/Research', 'research', $pages, [
            'projects' => Project::query()->published()->ordered()->get()
                ->map(fn (Project $project) => $project->toCard($locale))->values()->all(),
        ]);
    }

    private function render(string $component, string $route, PublicPageProps $pages, array $extra = []): Response
    {
        $locale = ContentLocale::fromRequest();

        return Inertia::render($component, $pages->base(
            $locale,
            PublicPageProps::languageUrls($route, "{$route}.en"),
            $extra,
        ));
    }
}
