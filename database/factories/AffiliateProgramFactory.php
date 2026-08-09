<?php

namespace Database\Factories;

use App\Models\AffiliateProgram;
use App\Models\Application;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AffiliateProgram>
 */
class AffiliateProgramFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company().' Afiliados';

        return [
            'application_id' => Application::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 999999),
            'website' => fake()->url(),
            'description' => fake()->sentence(),
            'provider' => AffiliateProgram::PROVIDER_MANUAL,
            'status' => AffiliateProgram::STATUS_ACTIVE,
            'configuration' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AffiliateProgram::STATUS_INACTIVE,
        ]);
    }
}
