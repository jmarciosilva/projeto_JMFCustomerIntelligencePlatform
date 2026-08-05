<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $occurredAt = fake()->dateTimeBetween('-1 week', 'now');

        return [
            'application_id' => Application::factory(),
            'tenant_id' => fn (array $attributes) => Application::findOrFail($attributes['application_id'])->tenant_id,
            'event_id' => (string) Str::ulid(),
            'event_name' => 'page.viewed',
            'visitor_id' => 'visitor_'.fake()->unique()->numberBetween(1, 999999),
            'session_id' => 'session_'.fake()->numberBetween(1, 999999),
            'contact_id' => null,
            'subject_type' => null,
            'subject_id' => null,
            'properties' => [],
            'context' => [],
            'occurred_at' => $occurredAt,
            'received_at' => $occurredAt,
        ];
    }
}
