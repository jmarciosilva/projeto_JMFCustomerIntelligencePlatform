<?php

namespace App\Domain\Affiliate;

use App\Models\AffiliateConversion;
use App\Models\AffiliateLink;
use App\Models\Application;
use Carbon\Carbon;

class CalculateAffiliateMetricsAction
{
    public function execute(Application $application, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfDay();

        $conversions = AffiliateConversion::where('application_id', $application->id)
            ->whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', [AffiliateConversion::STATUS_APPROVED, AffiliateConversion::STATUS_PAID])
            ->get();

        $campaignIds = \App\Models\Campaign::where('application_id', $application->id)
            ->pluck('id');

        $totalClicks = AffiliateLink::whereIn('campaign_id', $campaignIds)
            ->sum('clicks');

        $links = AffiliateLink::whereIn('campaign_id', $campaignIds)
            ->get();

        $totalImpressions = $links->count();
        $totalRevenue = $conversions->sum('commission_value');
        $totalCommissions = $conversions->sum('commission_value');
        $conversionCount = $conversions->count();

        $ctr = $totalImpressions > 0 ? ($totalClicks / $totalImpressions) * 100 : 0;
        $epc = $totalClicks > 0 ? ($totalCommissions / $totalClicks) : 0;

        return [
            'total_revenue' => $totalRevenue,
            'total_commissions' => $totalCommissions,
            'total_conversions' => $conversionCount,
            'total_clicks' => $totalClicks,
            'total_links' => $totalImpressions,
            'ctr' => round($ctr, 2),
            'epc' => round($epc, 2),
            'average_commission' => $conversionCount > 0 ? round($totalCommissions / $conversionCount, 2) : 0,
            'average_order_value' => $conversionCount > 0 ? round($totalRevenue / $conversionCount, 2) : 0,
            'period' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
            ],
        ];
    }
}
