<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\HeroSlide;
use App\Models\Journal;
use App\Models\JournalIssue;
use App\Models\Post;
use App\Models\Project;
use App\Services\ContentRepository;
use App\Services\ImageVariantService;
use App\Services\PublicPageProps;
use App\Support\ContentLocale;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(ContentRepository $contentRepository, ImageVariantService $images, PublicPageProps $pages): Response
    {
        $locale = ContentLocale::fromRequest();
        $props = $pages->base($locale, PublicPageProps::languageUrls('home', 'home.en'));
        $blocks = $props['blocks'];
        $canEdit = $props['canEdit'];

        // Ancien système d'annonce conservé pour compatibilité (remplacé par « announcements »)
        $props['blocks']['popup'] = $contentRepository->visitorPopup($blocks['popup'] ?? [], $locale);
        $props['visitorPopups'] = $canEdit ? $contentRepository->visitorPopups($locale) : [];

        $props['performance'] += [
            'heroImage' => $images->forUrl(
                $blocks['hero']['image'] ?? null,
                ImageVariantService::HERO_WIDTHS,
                ImageVariantService::HERO_SIZES,
            ),
            'contactMapImage' => $images->forUrl(
                $blocks['contact']['map_image'] ?? null,
                ImageVariantService::CONTACT_WIDTHS,
                ImageVariantService::CONTACT_SIZES,
            ),
        ];

        // Diaporama du héro (géré dans /admin/hero), liens résolus vers les contenus publics
        $props['heroSlides'] = HeroSlide::query()->live()->ordered()->with('linkable')->get()
            ->map(fn (HeroSlide $slide) => $slide->toPublic($locale))->filter()->values()->all();

        // La première diapositive est préchargée (image LCP de l'accueil)
        if ($first = $props['heroSlides'][0] ?? null) {
            $props['performance']['heroImage'] = $first['sources'];
        }

        // Actualités : l'article mis en avant passe en premier
        $props['latestPosts'] = Post::query()->published()->with('category', 'author')
            ->orderByDesc('is_featured')->latestPublished()->limit(3)->get()
            ->map(fn (Post $post) => $post->toCard($locale))->values()->all();

        $props['upcomingEvents'] = Event::query()->visible()->upcoming()->limit(4)->get()
            ->map(fn (Event $event) => $event->toCard($locale))->values()->all();

        $props['featuredJournals'] = Journal::query()->visible()->ordered()
            ->where('is_featured', true)->limit(3)->get()
            ->map(fn (Journal $journal) => $journal->toCard($locale))->values()->all();

        $props['projects'] = Project::query()->published()->ordered()->limit(6)->get()
            ->map(fn (Project $project) => $project->toCard($locale))->values()->all();

        // Dernier numéro publié d'une revue visible (journal trimestriel MaToY)
        $latestIssue = JournalIssue::query()->published()
            ->whereHas('journal', fn ($query) => $query->visible())
            ->with('journal')->orderByDesc('published_at')->latestFirst()->first();
        $props['latestIssue'] = $latestIssue?->toDetail($locale);

        return Inertia::render('Home', $props);
    }
}
