<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\CurationDecision;
use App\Models\ProductOpportunity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CurationDecision>
 */
class CurationDecisionFactory extends Factory
{
    protected $model = CurationDecision::class;

    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'product_opportunity_id' => ProductOpportunity::factory(),
            'user_id' => User::factory(),
            'decision' => $this->faker->randomElement([
                CurationDecision::DECISION_APPROVED,
                CurationDecision::DECISION_REJECTED,
            ]),
            'reason' => $this->faker->sentence(),
            'decided_at' => now(),
        ];
    }

    public function approved(): Factory
    {
        return $this->state([
            'decision' => CurationDecision::DECISION_APPROVED,
        ]);
    }

    public function rejected(): Factory
    {
        return $this->state([
            'decision' => CurationDecision::DECISION_REJECTED,
        ]);
    }
}
