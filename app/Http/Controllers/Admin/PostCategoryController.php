<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesEditorialInput;
use App\Http\Controllers\Controller;
use App\Models\PostCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PostCategoryController extends Controller
{
    use HandlesEditorialInput;

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        PostCategory::create([
            'name' => $this->translated($data, 'name'),
            'slug' => $this->uniqueSlug(PostCategory::class, null, $data['name']['fr']),
            'position' => (int) PostCategory::max('position') + 1,
        ]);

        return back(303)->with('success', 'Catégorie ajoutée.');
    }

    public function update(Request $request, PostCategory $postCategory): RedirectResponse
    {
        $data = $this->validated($request);

        $postCategory->update([
            'name' => $this->translated($data, 'name'),
            'slug' => $this->uniqueSlug(PostCategory::class, null, $data['name']['fr'], $postCategory),
        ]);

        return back(303)->with('success', 'Catégorie renommée.');
    }

    public function destroy(PostCategory $postCategory): RedirectResponse
    {
        // Les actualités liées restent publiées, sans catégorie (clé étrangère nullOnDelete)
        $postCategory->delete();

        return back(303)->with('success', 'Catégorie supprimée.');
    }

    private function validated(Request $request): array
    {
        return $request->validate($this->translatableRules('name', required: true, max: 80), [
            'name.fr.required' => 'Le nom en français est obligatoire.',
        ]);
    }
}
