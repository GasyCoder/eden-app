<?php

namespace Database\Factories;

use App\Models\Journal;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Journal>
 */
class JournalFactory extends Factory
{
    public function definition(): array
    {
        $title = 'Revue '.ucfirst($this->faker->unique()->words(2, true));

        return [
            'slug' => Str::slug($title),
            'title' => ['fr' => $title],
            'description' => ['fr' => '<p>'.$this->faker->paragraph().'</p>'],
            'domain' => 'Écologie',
            'issn' => '1234-5678',
            'status' => 'active',
            'is_featured' => false,
            'position' => 0,
        ];
    }
}
