<?php

namespace Database\Factories;

use App\Models\Announcement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Announcement>
 */
class AnnouncementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => ['fr' => 'Annonce '.$this->faker->word(), 'en' => 'Notice'],
            'message' => ['fr' => $this->faker->sentence()],
            'type' => 'info',
            'status' => 'published',
            'priority' => 1,
        ];
    }
}
