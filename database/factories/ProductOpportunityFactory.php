<?php

namespace Database\Factories;

use App\Domain\Affiliate\Enums\PurchaseIntentLabel;
use App\Domain\Affiliate\Enums\StatusSprintA;
use App\Models\AffiliateProduct;
use App\Models\Application;
use App\Models\ProductOpportunity;
use App\Models\Trend;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductOpportunity>
 */
class ProductOpportunityFactory extends Factory
{
    protected $model = ProductOpportunity::class;

    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'trend_id' => fn (array $attributes) => Trend::factory()->create([
                'application_id' => $attributes['application_id'] ?? Application::factory()->create()->id,
            ])->id,
            'affiliate_product_id' => fn (array $attributes) => AffiliateProduct::factory()->create([
                'application_id' => $attributes['application_id'] ?? Application::factory()->create()->id,
            ])->id,
            'status_sprint_a' => StatusSprintA::DISCOVERED,
            'discovery_opportunity_score' => $this->faker->numberBetween(0, 100),
            'opportunity_score_breakdown' => [
                'trend_score' => $this->faker->numberBetween(30, 100),
                'match_score' => $this->faker->numberBetween(30, 100),
                'intent_score' => $this->faker->numberBetween(0, 100),
                'commission' => $this->faker->numberBetween(5, 50),
                'popularity' => $this->faker->numberBetween(0, 100),
            ],
            'purchase_intent_score' => $this->faker->numberBetween(0, 100),
            'purchase_intent_label' => PurchaseIntentLabel::cases()[array_rand(PurchaseIntentLabel::cases())],
            'purchase_intent_breakdown' => [
                'base_intent' => $this->faker->randomElement(['INFORMATIONAL', 'INVESTIGATION', 'TRANSACTIONAL']),
                'adjustments' => [],
            ],
            'opportunity_score' => $this->faker->randomFloat(2, 30, 95),
            'opportunity_breakdown' => [
                'trend_score' => $this->faker->numberBetween(30, 100),
                'match_score' => $this->faker->numberBetween(30, 100),
                'intent' => $this->faker->randomElement(['high', 'medium', 'low']),
            ],
            'commercial_intent' => $this->faker->randomElement(['high', 'medium', 'low']),
            'status' => 'pending',
            'calculated_at' => now(),
        ];
    }
}
