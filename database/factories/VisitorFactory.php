<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\Visitor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Visitor>
 */
class VisitorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $seenAt = fake()->dateTimeBetween('-1 week', 'now');

        return [
            'application_id' => Application::factory(),
            'tenant_id' => fn (array $attributes) => Application::findOrFail($attributes['application_id'])->tenant_id,
            'contact_id' => null,
            'visitor_id' => 'visitor_'.fake()->unique()->numberBetween(1, 999999),
            'first_seen_at' => $seenAt,
            'last_seen_at' => $seenAt,
        ];
    }
}
