<?php

namespace Database\Factories;

use App\Models\Visitor;
use App\Models\VisitorSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VisitorSession>
 */
class VisitorSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startedAt = fake()->dateTimeBetween('-1 week', 'now');

        return [
            'visitor_id' => Visitor::factory(),
            'tenant_id' => fn (array $attributes) => Visitor::findOrFail($attributes['visitor_id'])->tenant_id,
            'application_id' => fn (array $attributes) => Visitor::findOrFail($attributes['visitor_id'])->application_id,
            'session_id' => 'session_'.fake()->unique()->numberBetween(1, 999999),
            'started_at' => $startedAt,
            'last_seen_at' => $startedAt,
        ];
    }
}
