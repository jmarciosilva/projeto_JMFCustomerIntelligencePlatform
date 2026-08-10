<?php

namespace App\Domain\Affiliate;

use App\Models\AffiliateLink;
use App\Models\Application;
use Carbon\Carbon;

class GetTopAffiliateProductsAction
{
    public function execute(Application $application, ?Carbon $startDate = null, ?Carbon $endDate = null, int $limit = 10): array
    {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfDay();

        $campaignIds = \App\Models\Campaign::where('application_id', $application->id)
            ->pluck('id');

        $links = AffiliateLink::whereIn('campaign_id', $campaignIds)
            ->with('product')
            ->get()
            ->map(function ($link) {
                return [
                    'product_id' => $link->affiliate_product_id,
                    'product_name' => $link->product->name,
                    'clicks' => $link->clicks,
                    'price' => $link->product->price,
                    'commission_rate' => $link->product->commission_percentage,
                ];
            })
            ->groupBy('product_id')
            ->map(function ($group) {
                return [
                    'product_name' => $group->first()['product_name'],
                    'total_clicks' => $group->sum('clicks'),
                    'price' => $group->first()['price'],
                    'commission_rate' => $group->first()['commission_rate'],
                ];
            })
            ->sortByDesc('total_clicks')
            ->take($limit)
            ->values()
            ->toArray();

        return $links;
    }
}
