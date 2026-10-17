<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesBulkActions;
use App\Http\Controllers\Admin\Concerns\HandlesEditorialInput;
use App\Http\Controllers\Admin\Concerns\NotifiesSubscribers;
use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostCategory;
use App\Support\EditorialDate;
use App\Support\Paginated;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    use HandlesBulkActions, HandlesEditorialInput, NotifiesSubscribers;

    public function index(Request $request): Response
    {
        $search = trim($request->string('q')->toString());
        $status = $request->string('status')->toString();
        $category = $request->integer('category');

        $posts = Post::query()->with('category', 'author')
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->where('title->fr', 'like', "%{$search}%")
                ->orWhere('title->en', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%")))
            ->when(in_array($status, Post::STATUSES, true), fn (Builder $query) => $query->where('status', $status))
            ->when($category > 0, fn (Builder $query) => $query->where('post_category_id', $category))
            ->latest('updated_at')
            ->paginate(15);

        return Inertia::render('Admin/Posts/Index', [
            'posts' => Paginated::from($posts, fn (Post $post) => $post->toAdminRow()),
            'filters' => ['q' => $search, 'status' => $status, 'category' => $category ?: null],
            'categories' => $this->categoryOptions(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Posts/Form', [
            'post' => (new Post(['status' => 'draft', 'is_featured' => false]))->toForm(),
            'categories' => $this->categoryOptions(),
            'newsletter' => $this->newsletterState(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $post = Post::create([...$this->payload($request), 'author_id' => $request->user()->id]);

        $notice = $this->notifySubscribers($request, $post);

        return to_route('admin.posts.edit', $post)->with('success', ($post->status === 'published' ? 'Actualité publiée.' : 'Brouillon enregistré.').$notice);
    }

    public function edit(Post $post): Response
    {
        return Inertia::render('Admin/Posts/Form', [
            'post' => $post->toForm(),
            'categories' => $this->categoryOptions(),
            'publicUrl' => route('news.show', $post->slug),
            'newsletter' => $this->newsletterState($post),
        ]);
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $post->update($this->payload($request, $post));

        return back(303)->with('success', 'Actualité enregistrée.'.$this->notifySubscribers($request, $post));
    }

    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        return to_route('admin.posts.index')->with('success', 'Actualité supprimée.');
    }

    private function payload(Request $request, ?Post $post = null): array
    {
        $data = $request->validate([
            ...$this->translatableRules('title', required: true),
            ...$this->translatableRules('excerpt', max: 600),
            ...$this->translatableRules('body', html: true),
            ...$this->translatableRules('seo_title', max: 70),
            ...$this->translatableRules('meta_description', max: 170),
            'slug' => ['nullable', 'string', 'max:190', 'alpha_dash'],
            'cover_image' => ['nullable', 'string', 'max:2048'],
            'post_category_id' => ['nullable', 'integer', 'exists:post_categories,id'],
            'author_name' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(Post::STATUSES)],
            'published_at' => ['nullable', 'date'],
            'is_featured' => ['boolean'],
        ], [
            'title.fr.required' => 'Le titre en français est obligatoire.',
        ]);

        $publishedAt = EditorialDate::fromInput($data['published_at'] ?? null);

        // Publier sans date = publier maintenant
        if ($data['status'] === 'published' && ! $publishedAt) {
            $publishedAt = $post?->published_at ?? now();
        }

        return [
            'title' => $this->translated($data, 'title'),
            'excerpt' => $this->translated($data, 'excerpt', 'textarea'),
            'body' => $this->translated($data, 'body', 'html'),
            'seo_title' => $this->translated($data, 'seo_title'),
            'meta_description' => $this->translated($data, 'meta_description', 'textarea'),
            'slug' => $this->uniqueSlug(Post::class, $data['slug'] ?? null, $data['title']['fr'] ?? null, $post),
            'cover_image' => $this->plain($data['cover_image'] ?? null, 'image'),
            'post_category_id' => $data['post_category_id'] ?? null,
            'author_name' => $this->plain($data['author_name'] ?? null),
            'status' => $data['status'],
            'published_at' => $publishedAt,
            'is_featured' => (bool) ($data['is_featured'] ?? false),
        ];
    }

    private function categoryOptions(): array
    {
        return PostCategory::query()->withCount('posts')->orderBy('position')->orderBy('id')->get()
            ->map(fn (PostCategory $category) => [
                'id' => $category->id,
                'slug' => $category->slug,
                'name' => $category->translate('name', 'fr'),
                'name_translations' => $category->translations('name'),
                'posts_count' => $category->posts_count,
            ])->values()->all();
    }

    protected function bulkModel(): string
    {
        return Post::class;
    }

    protected function bulkActions(): array
    {
        return [
            'publish' => ['%d actualité(s) publiée(s).', fn (Collection $posts) => $this->updateEach($posts, fn (Post $post) => ['status' => 'published', 'published_at' => $post->published_at ?? now()])],
            'draft' => ['%d actualité(s) repassée(s) en brouillon.', fn (Collection $posts) => $this->updateEach($posts, ['status' => 'draft'])],
            'feature' => ['%d actualité(s) mise(s) à la une.', fn (Collection $posts) => $this->updateEach($posts, ['is_featured' => true])],
            'unfeature' => ['%d actualité(s) retirée(s) de la une.', fn (Collection $posts) => $this->updateEach($posts, ['is_featured' => false])],
            'delete' => ['%d actualité(s) supprimée(s).', fn (Collection $posts) => $this->deleteEach($posts)],
        ];
    }
}
