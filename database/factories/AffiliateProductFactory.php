<?php

namespace Database\Factories;

use App\Models\AffiliateProduct;
use App\Models\AffiliateProgram;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AffiliateProduct>
 */
class AffiliateProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'affiliate_program_id' => AffiliateProgram::factory(),
            'application_id' => fn (array $attributes) => AffiliateProgram::find($attributes['affiliate_program_id'])?->application_id,
            'external_product_id' => fake()->unique()->numerify('MG-######'),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'category' => fake()->randomElement(['Casa', 'Tecnologia', 'Cozinha', 'Eletrônicos']),
            'brand' => fake()->company(),
            'price' => fake()->randomFloat(2, 49, 2999),
            'original_price' => fake()->randomFloat(2, 49, 3499),
            'commission_percentage' => fake()->randomFloat(2, 1, 12),
            'estimated_commission' => fake()->randomFloat(2, 1, 200),
            'affiliate_url' => fake()->url(),
            'image_url' => fake()->imageUrl(),
            'availability' => AffiliateProduct::AVAILABILITY_IN_STOCK,
            'last_checked_at' => now(),
            'metadata' => null,
        ];
    }
}
