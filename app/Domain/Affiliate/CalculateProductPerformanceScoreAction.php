<?php

namespace App\Domain\Affiliate;

use App\Models\AffiliateConversion;
use App\Models\AffiliateLink;
use App\Models\AffiliateProduct;
use App\Models\Application;
use App\Models\ProductPerformanceScore;
use Carbon\Carbon;

class CalculateProductPerformanceScoreAction
{
    public function execute(Application $application, ?Carbon $startDate = null, ?Carbon $endDate = null): void
    {
        $startDate = $startDate ?? now()->subDays(90);
        $endDate = $endDate ?? now()->endOfDay();

        $products = AffiliateProduct::whereHas('affiliateProgram', function ($query) {
            $query->where('application_id', auth()->user()?->application?->id ?? 1);
        })->get();

        foreach ($products as $product) {
            $this->calculateProductScore($application, $product, $startDate, $endDate);
        }
    }

    private function calculateProductScore(Application $application, AffiliateProduct $product, Carbon $startDate, Carbon $endDate): void
    {
        $campaignIds = \App\Models\Campaign::where('application_id', $application->id)->pluck('id');

        $totalClicks = AffiliateLink::where('affiliate_product_id', $product->id)
            ->whereIn('campaign_id', $campaignIds)
            ->sum('clicks');

        $conversions = AffiliateConversion::where('application_id', $application->id)
            ->where('affiliate_product_id', $product->id)
            ->whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', [AffiliateConversion::STATUS_APPROVED, AffiliateConversion::STATUS_PAID])
            ->get();

        $totalConversions = $conversions->count();
        $totalCommission = $conversions->sum('commission_value');
        $totalRevenue = $conversions->sum('order_value') ?? $conversions->sum('commission_value');

        $links = AffiliateLink::where('affiliate_product_id', $product->id)
            ->whereIn('campaign_id', $campaignIds)
            ->count();

        $ctrScore = $links > 0 ? min(($totalClicks / $links) * 10, 100) : 0;
        $conversionScore = $totalClicks > 0 ? min((($totalConversions / $totalClicks) * 100) * 0.5, 100) : 0;
        $salesScore = $totalConversions > 0 ? min(($totalConversions / 10) * 10, 100) : 0;
        $recurrenceScore = $this->calculateRecurrenceScore($application, $product);

        $performanceScore = ($ctrScore * 0.25) + ($conversionScore * 0.35) + ($salesScore * 0.25) + ($recurrenceScore * 0.15);

        ProductPerformanceScore::updateOrCreate(
            [
                'application_id' => $application->id,
                'affiliate_product_id' => $product->id,
            ],
            [
                'total_clicks' => $totalClicks,
                'total_conversions' => $totalConversions,
                'total_sales' => $totalConversions,
                'total_commission' => $totalCommission,
                'total_revenue' => $totalRevenue,
                'ctr_score' => round($ctrScore, 2),
                'conversion_score' => round($conversionScore, 2),
                'sales_score' => round($salesScore, 2),
                'recurrence_score' => round($recurrenceScore, 2),
                'performance_score' => round($performanceScore, 2),
                'calculated_at' => now(),
            ]
        );
    }

    private function calculateRecurrenceScore(Application $application, AffiliateProduct $product): float
    {
        $last30Days = AffiliateConversion::where('application_id', $application->id)
            ->where('affiliate_product_id', $product->id)
            ->where('order_date', '>=', now()->subDays(30))
            ->whereIn('status', [AffiliateConversion::STATUS_APPROVED, AffiliateConversion::STATUS_PAID])
            ->count();

        $last90Days = AffiliateConversion::where('application_id', $application->id)
            ->where('affiliate_product_id', $product->id)
            ->where('order_date', '>=', now()->subDays(90))
            ->whereIn('status', [AffiliateConversion::STATUS_APPROVED, AffiliateConversion::STATUS_PAID])
            ->count();

        if ($last90Days === 0) {
            return 0;
        }

        return min((($last30Days / $last90Days) * 100) / 3, 100);
    }
}
