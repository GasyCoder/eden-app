<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\PublicPageProps;
use App\Support\ContentLocale;
use App\Support\Paginated;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function index(Request $request, PublicPageProps $pages): Response
    {
        $locale = ContentLocale::fromRequest();
        $period = in_array($request->query('periode', $request->query('period')), ['past', 'passes'], true) ? 'past' : 'upcoming';

        $events = Event::query()->visible()
            ->when($period === 'past', fn ($query) => $query->past(), fn ($query) => $query->upcoming())
            ->paginate(9);

        return Inertia::render('Events/Index', $pages->base($locale, PublicPageProps::languageUrls('events.index', 'events.index.en'), [
            'period' => $period,
            'events' => Paginated::from($events, fn (Event $event) => $event->toCard($locale)),
            'counts' => [
                'upcoming' => Event::query()->visible()->upcoming()->count(),
                'past' => Event::query()->visible()->past()->count(),
            ],
        ]));
    }

    public function show(Request $request, Event $event, PublicPageProps $pages): Response
    {
        abort_unless($event->isPublic() || $request->user()?->is_admin, 404);

        $locale = ContentLocale::fromRequest();
        $detail = $event->toDetail($locale);

        return Inertia::render('Events/Show', $pages->base($locale, PublicPageProps::languageUrls('events.show', 'events.show.en', ['event' => $event->slug]), [
            'event' => $detail,
            'otherEvents' => Event::query()->visible()->upcoming()->whereKeyNot($event->id)->limit(3)->get()
                ->map(fn (Event $item) => $item->toCard($locale))->values()->all(),
            'pageSeo' => [
                'title' => $detail['title'],
                'description' => $detail['summary'],
                'image' => $event->cover_image,
                'type' => 'article',
            ],
        ]));
    }
}
