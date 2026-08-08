<?php

namespace App\Domain\Intelligence;

use App\Models\MarketplaceMetric;
use App\Models\SalesForecast;
use Illuminate\Support\Collection;

class ForecastEngine
{
    private const LOOKBACK_DAYS = 30;

    public function forecast(int $applicationId, int $horizonDays = 7, ?int $sellerId = null): array
    {
        $dailyRevenue = $this->getDailyRevenue($applicationId, $sellerId);
        $dailyPurchases = $this->getDailyPurchases($applicationId, $sellerId);

        if ($dailyRevenue->isEmpty()) {
            return [
                'predicted_revenue' => 0.0,
                'predicted_purchases' => 0,
                'confidence' => SalesForecast::CONFIDENCE_LOW,
                'method' => 'moving_average',
            ];
        }

        $avgDailyRevenue = $dailyRevenue->avg();
        $avgDailyPurchases = $dailyPurchases->avg();
        $trendFactor = $this->calculateTrendFactor($dailyRevenue);

        $predictedRevenue = round($avgDailyRevenue * $horizonDays * $trendFactor, 2);
        $predictedPurchases = (int) round($avgDailyPurchases * $horizonDays * $trendFactor);

        return [
            'predicted_revenue' => max(0.0, $predictedRevenue),
            'predicted_purchases' => max(0, $predictedPurchases),
            'confidence' => $this->calculateConfidence($dailyRevenue->count()),
            'method' => 'moving_average',
        ];
    }

    private function getDailyRevenue(int $applicationId, ?int $sellerId): Collection
    {
        $query = MarketplaceMetric::where('application_id', $applicationId)
            ->where('date', '>=', now()->subDays(self::LOOKBACK_DAYS)->toDateString());

        if ($sellerId) {
            $query->where('seller_id', $sellerId);
        }

        return $query->get()
            ->groupBy(fn ($m) => $m->date->toDateString())
            ->map(fn ($day) => (float) $day->sum('revenue'))
            ->sortKeys()
            ->values();
    }

    private function getDailyPurchases(int $applicationId, ?int $sellerId): Collection
    {
        $query = MarketplaceMetric::where('application_id', $applicationId)
            ->where('date', '>=', now()->subDays(self::LOOKBACK_DAYS)->toDateString());

        if ($sellerId) {
            $query->where('seller_id', $sellerId);
        }

        return $query->get()
            ->groupBy(fn ($m) => $m->date->toDateString())
            ->map(fn ($day) => (int) $day->sum('purchases'))
            ->sortKeys()
            ->values();
    }

    private function calculateTrendFactor(Collection $dailyValues): float
    {
        if ($dailyValues->count() < 4) {
            return 1.0;
        }

        $midpoint = (int) floor($dailyValues->count() / 2);
        $firstHalfAvg = $dailyValues->slice(0, $midpoint)->avg();
        $secondHalfAvg = $dailyValues->slice($midpoint)->avg();

        if ($firstHalfAvg <= 0) {
            return 1.0;
        }

        $trendFactor = $secondHalfAvg / $firstHalfAvg;

        // Bound the trend adjustment to avoid wild extrapolations (±50%)
        return max(0.5, min(1.5, $trendFactor));
    }

    private function calculateConfidence(int $dataPoints): string
    {
        return match (true) {
            $dataPoints >= 21 => SalesForecast::CONFIDENCE_HIGH,
            $dataPoints >= 7 => SalesForecast::CONFIDENCE_MEDIUM,
            default => SalesForecast::CONFIDENCE_LOW,
        };
    }
}
