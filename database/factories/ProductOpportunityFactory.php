<?php

namespace Database\Factories;

use App\Models\AffiliateProduct;
use App\Models\ProductOpportunity;
use App\Models\Trend;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductOpportunity>
 */
class ProductOpportunityFactory extends Factory
{
    protected $model = ProductOpportunity::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $intents = [ProductOpportunity::INTENT_HIGH, ProductOpportunity::INTENT_MEDIUM, ProductOpportunity::INTENT_LOW];

        return [
            'trend_id' => Trend::factory(),
            'affiliate_product_id' => AffiliateProduct::factory(),
            'opportunity_score' => $this->faker->numberBetween(30, 95),
            'opportunity_breakdown' => [
                'trend_score' => $this->faker->numberBetween(30, 100),
                'match_score' => $this->faker->numberBetween(30, 100),
                'commercial_intent' => $this->faker->randomElement([0, 25, 50, 100]),
                'commission' => $this->faker->numberBetween(0, 50),
                'popularity' => $this->faker->numberBetween(0, 100),
            ],
            'commercial_intent' => $this->faker->randomElement($intents),
            'status' => ProductOpportunity::STATUS_PENDING,
            'calculated_at' => now(),
        ];
    }
}
