<?php

namespace App\Domain\Intelligence;

use App\Models\MarketplaceMetric;
use App\Models\ProductTrend;
use Illuminate\Support\Collection;

class TrendAnalyzer
{
    public const RISING_THRESHOLD = 10.0;

    public const FALLING_THRESHOLD = -10.0;

    public function analyze(int $applicationId, int $periodDays = 7): Collection
    {
        $now = now();
        $currentStart = $now->copy()->subDays($periodDays)->startOfDay();
        $currentEnd = $now->copy()->endOfDay();
        $previousStart = $now->copy()->subDays($periodDays * 2)->startOfDay();
        $previousEnd = $now->copy()->subDays($periodDays)->subSecond()->endOfDay();

        $currentMetrics = $this->aggregateByProduct($applicationId, $currentStart, $currentEnd);
        $previousMetrics = $this->aggregateByProduct($applicationId, $previousStart, $previousEnd);

        $productIds = $currentMetrics->keys()->merge($previousMetrics->keys())->unique();

        return $productIds->map(function ($productId) use ($currentMetrics, $previousMetrics) {
            $current = $currentMetrics->get($productId, $this->emptyMetrics());
            $previous = $previousMetrics->get($productId, $this->emptyMetrics());

            $growthRate = $this->calculateGrowthRate($previous['views'], $current['views']);
            $direction = $this->classifyDirection($growthRate);

            return [
                'product_id' => $productId,
                'direction' => $direction,
                'growth_rate' => $growthRate,
                'current' => $current,
                'previous' => $previous,
            ];
        })->values();
    }

    private function aggregateByProduct(int $applicationId, $startDate, $endDate): Collection
    {
        return MarketplaceMetric::where('application_id', $applicationId)
            ->whereNotNull('product_id')
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->groupBy('product_id')
            ->map(fn ($metrics) => [
                'views' => (int) $metrics->sum('product_views'),
                'purchases' => (int) $metrics->sum('purchases'),
                'revenue' => (float) $metrics->sum('revenue'),
            ]);
    }

    private function calculateGrowthRate(int $previousViews, int $currentViews): float
    {
        if ($previousViews === 0) {
            return $currentViews > 0 ? 100.0 : 0.0;
        }

        return round((($currentViews - $previousViews) / $previousViews) * 100, 2);
    }

    private function classifyDirection(float $growthRate): string
    {
        return match (true) {
            $growthRate >= self::RISING_THRESHOLD => ProductTrend::DIRECTION_RISING,
            $growthRate <= self::FALLING_THRESHOLD => ProductTrend::DIRECTION_FALLING,
            default => ProductTrend::DIRECTION_STABLE,
        };
    }

    private function emptyMetrics(): array
    {
        return ['views' => 0, 'purchases' => 0, 'revenue' => 0.0];
    }
}
