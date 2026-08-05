<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\DailyMetric;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DailyMetric>
 */
class DailyMetricFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'tenant_id' => fn (array $attributes) => Application::findOrFail($attributes['application_id'])->tenant_id,
            'date' => now()->toDateString(),
            'metric_key' => 'events_total',
            'metric_value' => fake()->numberBetween(0, 1000),
        ];
    }
}
