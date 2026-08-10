<?php

namespace Database\Factories;

use App\Models\AffiliateProduct;
use App\Models\Trend;
use App\Models\TrendProductMatch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrendProductMatch>
 */
class TrendProductMatchFactory extends Factory
{
    protected $model = TrendProductMatch::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'trend_id' => Trend::factory(),
            'affiliate_product_id' => AffiliateProduct::factory(),
            'match_score' => $this->faker->numberBetween(50, 100),
            'match_breakdown' => [
                'keyword' => $this->faker->numberBetween(30, 100),
                'category' => $this->faker->numberBetween(0, 100),
                'brand' => $this->faker->numberBetween(0, 100),
            ],
        ];
    }
}
