<?php

namespace Database\Factories;

use App\Models\ContentPublication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContentPublication>
 */
class ContentPublicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'campaign_id' => fn() => \App\Models\Campaign::factory()->create()->id,
            'product_opportunity_id' => null,
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'content_type' => $this->faker->randomElement(['blog_post', 'social_media', 'email', 'video', 'other']),
            'platform' => $this->faker->randomElement(['instagram', 'facebook', 'whatsapp', 'email', 'website', 'other']),
            'url' => $this->faker->url(),
            'status' => $this->faker->randomElement(['draft', 'published', 'archived']),
            'published_at' => $this->faker->optional()->dateTime(),
        ];
    }
}
