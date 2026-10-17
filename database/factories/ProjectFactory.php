<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    public function definition(): array
    {
        $title = 'Projet '.ucfirst($this->faker->unique()->words(2, true));

        return [
            'slug' => Str::slug($title),
            'title' => ['fr' => $title],
            'summary' => ['fr' => $this->faker->sentence(12)],
            'progress' => 'ongoing',
            'team' => 'EAD 1',
            'funder' => 'AFD',
            'start_year' => 2024,
            'end_year' => 2027,
            'status' => 'published',
            'is_featured' => false,
            'position' => 0,
        ];
    }

    public function draft(): static
    {
        return $this->state(['status' => 'draft']);
    }

    public function withPage(): static
    {
        return $this->state(['details' => ['objectives' => ['Objectif A', 'Objectif B']]]);
    }
}
