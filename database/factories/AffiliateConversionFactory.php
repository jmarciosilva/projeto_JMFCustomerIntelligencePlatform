<?php

namespace Database\Factories;

use App\Models\AffiliateConversion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AffiliateConversion>
 */
class AffiliateConversionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'application_id' => fn() => \App\Models\Application::factory()->create()->id,
            'affiliate_product_id' => fn() => \App\Models\AffiliateProduct::factory()->create()->id,
            'affiliate_program_id' => fn() => \App\Models\AffiliateProgram::factory()->create()->id,
            'campaign_id' => null,
            'affiliate_link_id' => null,
            'order_reference' => $this->faker->unique()->bothify('PED-########'),
            'order_date' => $this->faker->dateTimeThisMonth(),
            'product_price' => $this->faker->randomFloat(2, 100, 5000),
            'commission_rate' => $this->faker->randomFloat(2, 5, 25),
            'commission_value' => $this->faker->randomFloat(2, 10, 500),
            'status' => $this->faker->randomElement(['pending', 'approved', 'paid', 'cancelled']),
            'notes' => $this->faker->sentence(),
        ];
    }
}
