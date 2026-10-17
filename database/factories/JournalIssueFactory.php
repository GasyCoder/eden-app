<?php

namespace Database\Factories;

use App\Models\Journal;
use App\Models\JournalIssue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JournalIssue>
 */
class JournalIssueFactory extends Factory
{
    public function definition(): array
    {
        $number = $this->faker->unique()->numberBetween(1, 99);

        return [
            'journal_id' => Journal::factory(),
            'slug' => sprintf('n%02d-2026', $number),
            'number' => $number,
            'year' => 2026,
            'period' => ['fr' => 'Juin - Août'],
            'editorial' => ['fr' => '<p>'.$this->faker->paragraph().'</p>'],
            'sections' => [
                ['rubric' => 'Actualités', 'title' => 'Atelier de restauration', 'summary' => $this->faker->sentence()],
                ['rubric' => 'Soutenances', 'title' => 'Thèse de doctorat', 'summary' => $this->faker->sentence()],
            ],
            'stats' => [['label' => 'Communication', 'value' => 13], ['label' => 'Formation', 'value' => 4]],
            'status' => 'published',
            'published_at' => now()->subDay(),
        ];
    }

    public function draft(): static
    {
        return $this->state(['status' => 'draft']);
    }
}
