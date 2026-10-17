<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Models\JournalIssue;
use App\Services\PublicPageProps;
use App\Support\ContentLocale;
use App\Support\Paginated;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class JournalController extends Controller
{
    public function index(Request $request, PublicPageProps $pages): Response
    {
        $locale = ContentLocale::fromRequest();
        $domain = trim($request->string('domaine')->toString() ?: $request->string('domain')->toString());
        $search = trim($request->string('q')->toString());

        $journals = Journal::query()->visible()->with('issues')
            ->when($domain !== '', fn (Builder $query) => $query->where('domain', $domain))
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($search, $locale): void {
                foreach (array_unique([$locale, ContentLocale::DEFAULT]) as $lang) {
                    $query->orWhere("title->{$lang}", 'like', "%{$search}%");
                }

                $query->orWhere('issn', 'like', "%{$search}%")->orWhere('editor', 'like', "%{$search}%");
            }))
            ->ordered()
            ->paginate(12);

        return Inertia::render('Journals/Index', $pages->base($locale, PublicPageProps::languageUrls('journals.index', 'journals.index.en'), [
            'journals' => Paginated::from($journals, fn (Journal $journal) => $journal->toCard($locale)),
            'domains' => Journal::query()->visible()->whereNotNull('domain')->where('domain', '<>', '')
                ->distinct()->orderBy('domain')->pluck('domain')->values()->all(),
            'filters' => ['domain' => $domain, 'q' => $search],
        ]));
    }

    public function show(Request $request, Journal $journal, PublicPageProps $pages): Response
    {
        abort_unless($journal->isPublic() || $request->user()?->is_admin, 404);

        $locale = ContentLocale::fromRequest();
        $detail = $journal->toDetail($locale);

        $issues = $journal->issues()
            ->when(! $request->user()?->is_admin, fn (Builder $query) => $query->published())
            ->latestFirst()->get()
            ->map(fn (JournalIssue $issue) => [...$issue->toCard($locale), 'is_public' => $issue->isPublic()])
            ->values()->all();

        return Inertia::render('Journals/Show', $pages->base($locale, PublicPageProps::languageUrls('journals.show', 'journals.show.en', ['journal' => $journal->slug]), [
            'journal' => $detail,
            'issues' => $issues,
            'related' => Journal::query()->visible()->whereKeyNot($journal->id)
                ->when($journal->domain, fn (Builder $query) => $query->orderByRaw('domain = ? desc', [$journal->domain]))
                ->ordered()->limit(3)->get()
                ->map(fn (Journal $item) => $item->toCard($locale))->values()->all(),
            'pageSeo' => [
                'title' => $detail['title'],
                'description' => $detail['summary'],
                'image' => $journal->cover_image,
                'type' => 'website',
            ],
        ]));
    }

    public function issue(Request $request, Journal $journal, JournalIssue $issue, PublicPageProps $pages): Response
    {
        $isAdmin = (bool) $request->user()?->is_admin;
        abort_unless(($journal->isPublic() && $issue->isPublic()) || $isAdmin, 404);

        $locale = ContentLocale::fromRequest();
        $issue->setRelation('journal', $journal);
        $detail = $issue->toDetail($locale);
        $journalTitle = $journal->translate('title', $locale);

        return Inertia::render('Journals/Issue', $pages->base($locale, PublicPageProps::languageUrls('journals.issues.show', 'journals.issues.show.en', ['journal' => $journal->slug, 'issue' => $issue->slug]), [
            'issue' => $detail,
            'journal' => $journal->toCard($locale),
            'otherIssues' => $journal->issues()->published()->whereKeyNot($issue->id)->latestFirst()->limit(4)->get()
                ->each(fn (JournalIssue $item) => $item->setRelation('journal', $journal))
                ->map(fn (JournalIssue $item) => $item->toCard($locale))->values()->all(),
            'pageSeo' => [
                'title' => trim("{$journalTitle} {$detail['label']}"),
                'description' => $detail['excerpt'],
                'image' => $issue->cover_image ?: $journal->cover_image,
                'type' => 'article',
            ],
        ]));
    }
}
