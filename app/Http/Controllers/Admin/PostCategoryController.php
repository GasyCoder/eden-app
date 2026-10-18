<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesEditorialInput;
use App\Http\Controllers\Controller;
use App\Models\PostCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PostCategoryController extends Controller
{
    use HandlesEditorialInput;

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        try {
            $data = $this->validated($request);
        } catch (ValidationException $exception) {
            if ($request->expectsJson() && ! $request->header('X-Inertia')) {
                return response()->json(['message' => 'Veuillez corriger les champs signalés.', 'errors' => $exception->errors()], 422);
            }
            throw $exception;
        }

        $category = PostCategory::create([
            'name' => $this->translated($data, 'name'),
            'slug' => $this->uniqueSlug(PostCategory::class, null, $data['name']['fr']),
            'position' => (int) PostCategory::max('position') + 1,
        ]);

        if ($request->expectsJson() && ! $request->header('X-Inertia')) {
            return response()->json(['category' => [
                ...$category->toOption('fr'),
                'name_translations' => $category->translations('name'),
                'posts_count' => 0,
            ]], 201);
        }

        return to_route('admin.posts.index', [], 303)->with('success', 'Catégorie ajoutée.');
    }

    public function update(Request $request, PostCategory $postCategory): RedirectResponse
    {
        $data = $this->validated($request);

        $postCategory->update([
            'name' => $this->translated($data, 'name'),
            'slug' => $this->uniqueSlug(PostCategory::class, null, $data['name']['fr'], $postCategory),
        ]);

        return to_route('admin.posts.index', [], 303)->with('success', 'Catégorie renommée.');
    }

    public function destroy(PostCategory $postCategory): RedirectResponse
    {
        // Les actualités liées restent publiées, sans catégorie (clé étrangère nullOnDelete)
        $postCategory->delete();

        return to_route('admin.posts.index', [], 303)->with('success', 'Catégorie placée dans la corbeille.');
    }

    private function validated(Request $request): array
    {
        return $request->validate($this->translatableRules('name', required: true, max: 80), [
            'name.fr.required' => 'Le nom en français est obligatoire.',
        ]);
    }
}
