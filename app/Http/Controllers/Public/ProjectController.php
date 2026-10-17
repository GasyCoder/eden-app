<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\PublicPageProps;
use App\Support\ContentLocale;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(Request $request, PublicPageProps $pages): Response
    {
        $locale = ContentLocale::fromRequest();
        $progress = $request->string('etat')->toString() ?: $request->string('progress')->toString();

        $projects = Project::query()->published()
            ->when(in_array($progress, Project::PROGRESS, true), fn ($query) => $query->where('progress', $progress))
            ->ordered()->get();

        return Inertia::render('Projects/Index', $pages->base($locale, PublicPageProps::languageUrls('projects.index', 'projects.index.en'), [
            'projects' => $projects->map(fn (Project $project) => $project->toCard($locale))->values()->all(),
            'filters' => ['progress' => in_array($progress, Project::PROGRESS, true) ? $progress : null],
        ]));
    }

    public function show(Request $request, Project $project, PublicPageProps $pages): Response
    {
        abort_unless($project->isPublic() || $request->user()?->is_admin, 404);

        $locale = ContentLocale::fromRequest();
        $detail = $project->toDetail($locale);

        return Inertia::render('Projects/Show', $pages->base($locale, PublicPageProps::languageUrls('projects.show', 'projects.show.en', ['project' => $project->slug]), [
            'project' => $detail,
            'otherProjects' => Project::query()->published()->whereKeyNot($project->id)->ordered()->limit(3)->get()
                ->map(fn (Project $item) => $item->toCard($locale))->values()->all(),
            'pageSeo' => [
                'title' => $detail['title'],
                'description' => $detail['summary'],
                'image' => $project->cover_image,
                'type' => 'website',
            ],
        ]));
    }
}
