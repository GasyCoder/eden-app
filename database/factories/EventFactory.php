<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    public function definition(): array
    {
        $title = rtrim($this->faker->unique()->sentence(4), '.');

        return [
            'slug' => Str::slug($title),
            'title' => ['fr' => $title],
            'summary' => ['fr' => $this->faker->sentence(12)],
            'starts_at' => now()->addWeek(),
            'ends_at' => now()->addWeek()->addHours(3),
            'format' => 'in_person',
            'location' => 'Université de Mahajanga',
            'status' => 'published',
            'is_featured' => false,
        ];
    }

    public function past(): static
    {
        return $this->state(['starts_at' => now()->subMonth(), 'ends_at' => now()->subMonth()->addHours(2)]);
    }
}
