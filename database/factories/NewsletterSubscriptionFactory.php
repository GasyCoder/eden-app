<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class NewsletterSubscriptionFactory extends Factory
{
    public function definition(): array
    {
        return ['email' => fake()->unique()->safeEmail(), 'status' => 'pending', 'locale' => 'fr', 'source' => 'site'];
    }

    public function verified(): static
    {
        return $this->state(fn () => ['status' => 'pending_approval', 'email_verified_at' => now()]);
    }

    public function active(): static
    {
        return $this->verified()->state(fn () => ['status' => 'subscribed', 'approved_at' => now(), 'subscribed_at' => now()]);
    }
}
