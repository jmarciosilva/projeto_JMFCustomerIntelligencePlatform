<?php

namespace App\Application\Affiliate\Actions;

use App\Domain\Affiliate\OpportunityScoreCalculator;
use App\Models\ProductOpportunity;
use App\Models\TrendProductMatch;

class CalculateOpportunitiesAction
{
    public function __construct(private readonly OpportunityScoreCalculator $calculator) {}

    /**
     * Calcula oportunidades para todos os product matches de uma application
     *
     * @return int quantidade de oportunidades calculadas
     */
    public function handle(?int $applicationId = null): int
    {
        $updated = 0;

        $matches = TrendProductMatch::query()
            ->with('trend', 'product')
            ->when($applicationId, fn ($q) => $q->whereHas('trend', fn ($q) => $q->where('application_id', $applicationId)))
            ->get();

        foreach ($matches as $match) {
            $result = $this->calculator->calculate($match->trend, $match->product);

            ProductOpportunity::updateOrCreate(
                [
                    'trend_id' => $match->trend_id,
                    'affiliate_product_id' => $match->affiliate_product_id,
                ],
                [
                    'opportunity_score' => $result['score'],
                    'opportunity_breakdown' => $result['breakdown'],
                    'commercial_intent' => $result['intent'],
                    'calculated_at' => now(),
                ]
            );

            $updated++;
        }

        return $updated;
    }

    /**
     * Processa oportunidade para um match específico
     */
    public function handleOne(TrendProductMatch $match): bool
    {
        $result = $this->calculator->calculate($match->trend, $match->product);

        ProductOpportunity::updateOrCreate(
            [
                'trend_id' => $match->trend_id,
                'affiliate_product_id' => $match->affiliate_product_id,
            ],
            [
                'opportunity_score' => $result['score'],
                'opportunity_breakdown' => $result['breakdown'],
                'commercial_intent' => $result['intent'],
                'calculated_at' => now(),
            ]
        );

        return true;
    }
}
