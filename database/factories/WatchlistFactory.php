<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\Watchlist;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Watchlist>
 */
class WatchlistFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'name' => fake()->unique()->words(2, true),
            'description' => fake()->sentence(),
            'keywords' => ['cafeteira', 'cafeteira espresso'],
            'hashtags' => ['#cafeteira', '#cantinhodocafe'],
            'categories' => ['Casa'],
            'collection_frequency' => Watchlist::FREQUENCY_DAILY,
            'status' => Watchlist::STATUS_ACTIVE,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Watchlist::STATUS_INACTIVE,
        ]);
    }
}
