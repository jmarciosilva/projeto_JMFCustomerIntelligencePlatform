<?php

namespace Database\Factories;

use App\Models\Trend;
use App\Models\Watchlist;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Trend>
 */
class TrendFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'watchlist_id' => Watchlist::factory(),
            'application_id' => fn (array $attributes) => Watchlist::find($attributes['watchlist_id'])?->application_id,
            'term' => fake()->unique()->words(2, true),
            'type' => Trend::TYPE_KEYWORD,
            'status' => Trend::STATUS_ACTIVE,
            'last_collected_at' => null,
        ];
    }
}
