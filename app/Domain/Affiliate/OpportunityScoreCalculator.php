<?php

namespace App\Domain\Affiliate;

use App\Models\AffiliateProduct;
use App\Models\Trend;
use App\Models\TrendProductMatch;

class OpportunityScoreCalculator
{
    public function __construct(private CommercialIntentClassifier $intentClassifier) {}

    /**
     * Calcula score de oportunidade (0-100) combinando múltiplos fatores
     *
     * Fórmula ponderada:
     * - Trend Score (0-100): 35%
     * - Match Score (0-100): 25%
     * - Commercial Intent (0/25/50/100): 20%
     * - Commission (% normalizado): 10%
     * - Product Popularity (0-100): 10%
     */
    public function calculate(Trend $trend, AffiliateProduct $product): array
    {
        // Trend Score (35%)
        $trendScore = (float) ($trend->trend_score ?? 0);

        // Match Score (25%)
        $match = TrendProductMatch::where('trend_id', $trend->id)
            ->where('affiliate_product_id', $product->id)
            ->first();
        $matchScore = $match?->match_score ?? 0;

        // Commercial Intent (20%)
        $intent = $this->intentClassifier->classify($trend->term);
        $intentScore = $this->intentClassifier->scoreByIntent($intent);

        // Commission (10%) - normalizar para 0-100
        $commission = (float) ($product->commission_percentage ?? 0);
        $commissionScore = min($commission * 2, 100); // Máx 50% de comissão = 100

        // Product Popularity (10%) - baseado em contagem de events
        $popularityScore = $this->calculateProductPopularity($product);

        // Calcular score ponderado
        $score = ($trendScore * 0.35) +
                ($matchScore * 0.25) +
                ($intentScore * 0.20) +
                ($commissionScore * 0.10) +
                ($popularityScore * 0.10);

        $score = min(round($score, 2), 100);

        return [
            'score' => $score,
            'breakdown' => [
                'trend_score' => round($trendScore, 2),
                'match_score' => round($matchScore, 2),
                'commercial_intent' => round($intentScore, 2),
                'commission' => round($commissionScore, 2),
                'popularity' => round($popularityScore, 2),
            ],
            'intent' => $intent,
        ];
    }

    /**
     * Calcula score de popularidade do produto (0-100) baseado em eventos
     */
    private function calculateProductPopularity(AffiliateProduct $product): float
    {
        // Contar eventos do produto em sua application nos últimos 90 dias
        $eventCount = \App\Models\Event::where('application_id', $product->application_id)
            ->where('subject_type', 'product')
            ->where('subject_id', $product->id)
            ->where('occurred_at', '>=', now()->subDays(90))
            ->count();

        // Escala: 0 eventos = 0, 1000+ eventos = 100
        // Logarítmica para não dar peso excessivo a produtos já muito populares
        if ($eventCount === 0) {
            return 0;
        }

        if ($eventCount >= 1000) {
            return 100;
        }

        // log10(1000) = 3, então dividir por 3 para normalizar a 0-100
        return min((log10($eventCount + 1) / 3) * 100, 100);
    }
}
