<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostCategory;
use App\Services\PublicPageProps;
use App\Support\ContentLocale;
use App\Support\Paginated;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NewsController extends Controller
{
    public function index(Request $request, PublicPageProps $pages): Response
    {
        $locale = ContentLocale::fromRequest();
        $category = $request->string('categorie')->toString() ?: $request->string('category')->toString();
        $search = trim($request->string('q')->toString());
        $isFiltered = $category !== '' || $search !== '';

        $query = Post::query()->visible()->with('category', 'author')
            ->when($category !== '', fn (Builder $query) => $query->whereHas('category', fn (Builder $query) => $query->where('slug', $category)))
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($search, $locale): void {
                foreach (array_unique([$locale, ContentLocale::DEFAULT]) as $lang) {
                    $query->orWhere("title->{$lang}", 'like', "%{$search}%")
                        ->orWhere("excerpt->{$lang}", 'like', "%{$search}%");
                }
            }));

        // L'article à la une n'est mis en avant que sur la première page non filtrée
        $featured = null;

        if (! $isFiltered && $request->integer('page', 1) === 1) {
            $featured = (clone $query)->where('status', 'published')->where('is_featured', true)->latestPublished()->first();
        }

        $posts = (clone $query)
            ->when($featured, fn (Builder $query) => $query->whereKeyNot($featured->id))
            ->latestPublished()
            ->paginate(9);

        return Inertia::render('News/Index', $pages->base($locale, PublicPageProps::languageUrls('news.index', 'news.index.en'), [
            'featured' => $featured?->toCard($locale),
            'posts' => Paginated::from($posts, fn (Post $post) => $post->toCard($locale)),
            'categories' => PostCategory::query()
                ->withCount(['posts' => fn (Builder $query) => $query->visible()])
                ->orderBy('position')->orderBy('id')->get()
                ->filter(fn (PostCategory $category) => $category->posts_count > 0)
                ->map(fn (PostCategory $category) => [...$category->toOption($locale), 'count' => $category->posts_count])
                ->values()->all(),
            'filters' => ['category' => $category, 'q' => $search],
        ]));
    }

    public function show(Request $request, Post $post, PublicPageProps $pages): Response
    {
        abort_unless($post->isPublic() || $request->user()?->is_admin, 404);

        $locale = ContentLocale::fromRequest();
        $post->load('category', 'author');

        $recent = Post::query()->published()->with('category', 'author')
            ->whereKeyNot($post->id)
            ->when($post->post_category_id, fn (Builder $query) => $query->orderByRaw('post_category_id = ? desc', [$post->post_category_id]))
            ->latestPublished()->limit(3)->get();

        $detail = $post->toDetail($locale);

        return Inertia::render('News/Show', $pages->base($locale, PublicPageProps::languageUrls('news.show', 'news.show.en', ['post' => $post->slug]), [
            'post' => $detail,
            'recentPosts' => $recent->map(fn (Post $item) => $item->toCard($locale))->values()->all(),
            'pageSeo' => [
                'title' => $detail['seo_title'],
                'description' => $detail['meta_description'],
                'image' => $post->cover_image,
                'type' => 'article',
            ],
        ]));
    }
}
