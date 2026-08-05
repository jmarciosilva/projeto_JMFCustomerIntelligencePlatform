<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\ProductAffinity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductAffinity>
 */
class ProductAffinityFactory extends Factory
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
            'subject_type' => 'Article',
            'subject_id_a' => (string) fake()->unique()->numberBetween(1, 999999),
            'subject_id_b' => (string) fake()->unique()->numberBetween(1, 999999),
            'co_occurrences' => fake()->numberBetween(1, 50),
            'computed_at' => now(),
        ];
    }
}
