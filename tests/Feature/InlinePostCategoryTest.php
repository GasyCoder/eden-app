<?php

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;

test('an editor can create a category in JSON without navigating or changing the article', function () {
    $editor = User::factory()->create(['is_admin' => true, 'role' => 'editor']);
    $post = Post::factory()->create();
    $title = $post->title;
    $response = $this->actingAs($editor)->postJson('/admin/post-categories', ['name' => ['fr' => 'Vie doctorale', 'en' => 'Doctoral life']])
        ->assertCreated()->assertHeaderMissing('Location')
        ->assertJsonPath('category.name', 'Vie doctorale')->assertJsonPath('category.name_translations.en', 'Doctoral life');
    expect(PostCategory::findOrFail($response->json('category.id'))->slug)->toBe('vie-doctorale')
        ->and($post->fresh()->title)->toBe($title);
});

test('inline category validation returns JSON errors without a redirect', function () {
    $editor = User::factory()->create(['is_admin' => true, 'role' => 'editor']);
    $this->actingAs($editor)->postJson('/admin/post-categories', ['name' => ['fr' => '', 'en' => 'Only English']])
        ->assertUnprocessable()->assertHeaderMissing('Location')->assertJsonValidationErrors('name.fr');
});

test('inline category slugs also reserve deleted categories so they can be restored', function () {
    $category = PostCategory::factory()->create(['name' => ['fr' => 'Recherche'], 'slug' => 'recherche']);
    $category->delete();
    $editor = User::factory()->create(['is_admin' => true, 'role' => 'editor']);
    $this->actingAs($editor)->postJson('/admin/post-categories', ['name' => ['fr' => 'Recherche']])
        ->assertCreated()->assertJsonPath('category.slug', 'recherche-2');
});

test('guests and ordinary users cannot create inline categories', function () {
    $this->postJson('/admin/post-categories', ['name' => ['fr' => 'Interdite']])->assertNotFound();
    $this->actingAs(User::factory()->create(['is_admin' => false]))
        ->postJson('/admin/post-categories', ['name' => ['fr' => 'Interdite']])->assertNotFound();
});
