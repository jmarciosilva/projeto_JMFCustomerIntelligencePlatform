<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contact>
 */
class ContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $identifiedAt = fake()->dateTimeBetween('-1 week', 'now');

        return [
            'tenant_id' => Tenant::factory(),
            'external_id' => null,
            'email' => fake()->unique()->safeEmail(),
            'phone' => null,
            'name' => fake()->name(),
            'properties' => [],
            'first_identified_at' => $identifiedAt,
            'last_seen_at' => $identifiedAt,
        ];
    }
}
