<?php

namespace Database\Factories;

use App\Models\Trend;
use App\Models\TrendSnapshot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrendSnapshot>
 */
class TrendSnapshotFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'trend_id' => Trend::factory(),
            'source' => TrendSnapshot::SOURCE_MANUAL,
            'score' => null,
            'mentions' => fake()->numberBetween(1, 200),
            'engagement' => fake()->numberBetween(0, 500),
            'velocity' => fake()->randomFloat(2, -50, 100),
            'metadata' => null,
            'collected_at' => now(),
        ];
    }
}
