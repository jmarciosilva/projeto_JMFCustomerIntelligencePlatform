<?php

namespace Database\Factories;

use App\Models\AffiliateLink;
use App\Models\AffiliateProduct;
use App\Models\Campaign;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AffiliateLinkFactory extends Factory
{
    protected $model = AffiliateLink::class;

    public function definition(): array
    {
        return [
            'campaign_id' => Campaign::factory(),
            'affiliate_product_id' => AffiliateProduct::factory(),
            'slug' => Str::slug($this->faker->words(3, true)),
            'affiliate_url' => $this->faker->url(),
            'utm_source' => $this->faker->randomElement(['instagram', 'facebook', 'email', 'website']),
            'utm_medium' => $this->faker->randomElement(['post', 'story', 'email', 'organic']),
            'utm_campaign' => Str::slug($this->faker->words(2, true)),
            'clicks' => 0,
            'status' => AffiliateLink::STATUS_ACTIVE,
        ];
    }
}
