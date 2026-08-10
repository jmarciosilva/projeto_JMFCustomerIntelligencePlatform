<?php

namespace App\Domain\Affiliate;

use App\Models\Application;
use App\Models\ProductOpportunity;
use App\Models\ProductPerformanceScore;

class JmfRecommendationEngineAction
{
    public function execute(Application $application, int $limit = 10): array
    {
        $trendIds = \App\Models\Trend::whereHas('watchlist', function ($query) use ($application) {
            $query->where('application_id', $application->id);
        })->pluck('id');

        $recommendations = ProductOpportunity::whereIn('trend_id', $trendIds)
            ->with(['product', 'trend'])
            ->get()
            ->map(function ($opportunity) use ($application) {
                $performanceScore = ProductPerformanceScore::where('application_id', $application->id)
                    ->where('affiliate_product_id', $opportunity->affiliate_product_id)
                    ->first();

                $trendScore = $opportunity->trend?->trend_score ?? 0;
                $opportunityScore = $opportunity->opportunity_score ?? 0;
                $performanceScore = $performanceScore?->performance_score ?? 0;

                $confidenceScore = ($trendScore * 0.35) + ($opportunityScore * 0.45) + ($performanceScore * 0.20);

                $reasons = [];
                if ($trendScore >= 75) {
                    $reasons[] = "Tendência forte ({$trendScore}/100)";
                }
                if ($opportunityScore >= 75) {
                    $reasons[] = "Oportunidade alta ({$opportunityScore}/100)";
                }
                if ($performanceScore >= 75) {
                    $reasons[] = "Desempenho excelente ({$performanceScore}/100)";
                }
                if (empty($reasons)) {
                    $reasons[] = "Combinação equilibrada de scores";
                }

                return [
                    'product_id' => $opportunity->affiliate_product_id,
                    'product_name' => $opportunity->product->name,
                    'trend_score' => round($trendScore, 2),
                    'opportunity_score' => round($opportunityScore, 2),
                    'performance_score' => round($performanceScore, 2),
                    'confidence_score' => round($confidenceScore, 2),
                    'reasons' => $reasons,
                    'trend_term' => $opportunity->trend?->term ?? 'N/A',
                ];
            })
            ->sortByDesc('confidence_score')
            ->take($limit)
            ->values()
            ->toArray();

        return $recommendations;
    }
}
