<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    public function definition(): array
    {
        $title = rtrim($this->faker->unique()->sentence(6), '.');

        return [
            'slug' => Str::slug($title),
            'title' => ['fr' => $title, 'en' => "EN {$title}"],
            'excerpt' => ['fr' => $this->faker->sentence(18)],
            'body' => ['fr' => '<p>'.$this->faker->paragraph(4).'</p>'],
            'status' => 'published',
            'published_at' => now()->subDay(),
            'is_featured' => false,
        ];
    }

    public function draft(): static
    {
        return $this->state(['status' => 'draft', 'published_at' => null]);
    }
}
