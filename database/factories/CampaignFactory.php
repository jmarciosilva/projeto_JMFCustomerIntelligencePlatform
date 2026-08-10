<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\Campaign;
use Illuminate\Database\Eloquent\Factories\Factory;

class CampaignFactory extends Factory
{
    protected $model = Campaign::class;

    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'status' => Campaign::STATUS_ACTIVE,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
            'expected_clicks' => $this->faker->numberBetween(100, 1000),
            'expected_conversions' => $this->faker->numberBetween(10, 100),
        ];
    }
}
