<?php

namespace App\Domain\Marketplace;

use App\Models\Event;
use App\Models\MarketplaceMetric;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class GetSellerAnalyticsAction
{
    public function handle(int $applicationId, int $sellerId, ?int $days = 7): array
    {
        $startDate = now()->subDays($days)->startOfDay();
        $endDate = now()->endOfDay();

        $metrics = MarketplaceMetric::where('application_id', $applicationId)
            ->forSeller($sellerId)
            ->forDateRange($startDate->toDateString(), $endDate->toDateString())
            ->orderBy('date', 'desc')
            ->get();

        $events = Event::where('application_id', $applicationId)
            ->whereBetween('occurred_at', [$startDate, $endDate])
            ->whereJsonContains('properties->seller_id', $sellerId)
            ->get();

        return [
            'seller_id' => $sellerId,
            'period' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
                'days' => $days,
            ],
            'totals' => $this->calculateTotals($metrics, $events),
            'daily' => $this->getDailyTrend($metrics),
            'products' => $this->getTopProducts($applicationId, $sellerId, $startDate, $endDate),
            'conversion_funnel' => $this->getConversionFunnel($applicationId, $sellerId, $startDate, $endDate),
        ];
    }

    private function calculateTotals(Collection $metrics, Collection $events): array
    {
        $totalMetrics = [
            'product_views' => $metrics->sum('product_views'),
            'product_favorites' => $metrics->sum('product_favorites'),
            'cart_adds' => $metrics->sum('cart_adds'),
            'cart_views' => $metrics->sum('cart_views'),
            'cart_abandonments' => $metrics->sum('cart_abandonments'),
            'checkout_starts' => $metrics->sum('checkout_starts'),
            'purchases' => $metrics->sum('purchases'),
            'reviews' => $metrics->sum('reviews'),
            'unique_visitors' => $metrics->sum('unique_visitors'),
            'unique_buyers' => $metrics->sum('unique_buyers'),
            'revenue' => $metrics->sum('revenue'),
            'avg_order_value' => $metrics->avg('avg_order_value'),
        ];

        $conversions = $events->where('event_name', 'purchase.completed')->count();
        $viewsCount = $events->where('event_name', 'product.viewed')->count();
        $conversionRate = $viewsCount > 0 ? ($conversions / $viewsCount) * 100 : 0;

        return array_merge($totalMetrics, [
            'conversion_rate' => round($conversionRate, 2),
        ]);
    }

    private function getDailyTrend(Collection $metrics): array
    {
        return $metrics
            ->groupBy('date')
            ->map(fn (Collection $day) => [
                'date' => $day->first()->date->toDateString(),
                'product_views' => $day->sum('product_views'),
                'purchases' => $day->sum('purchases'),
                'revenue' => $day->sum('revenue'),
            ])
            ->values()
            ->toArray();
    }

    private function getTopProducts(int $applicationId, int $sellerId, Carbon $startDate, Carbon $endDate): array
    {
        return MarketplaceMetric::where('application_id', $applicationId)
            ->forSeller($sellerId)
            ->whereNotNull('product_id')
            ->forDateRange($startDate->toDateString(), $endDate->toDateString())
            ->groupBy('product_id')
            ->selectRaw('product_id, SUM(product_views) as views, SUM(purchases) as purchases, SUM(revenue) as revenue')
            ->orderBy('views', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }

    private function getConversionFunnel(int $applicationId, int $sellerId, Carbon $startDate, Carbon $endDate): array
    {
        $events = Event::where('application_id', $applicationId)
            ->whereBetween('occurred_at', [$startDate, $endDate])
            ->whereJsonContains('properties->seller_id', $sellerId)
            ->get();

        $eventNames = [
            'product.viewed',
            'product.favorited',
            'cart.item_added',
            'cart.viewed',
            'checkout.started',
            'purchase.completed',
        ];

        $funnelData = [];
        $previousCount = null;

        foreach ($eventNames as $eventName) {
            $count = $events->where('event_name', $eventName)->count();
            $dropoff = $previousCount ? round(((($previousCount - $count) / $previousCount) * 100), 2) : 0;

            $funnelData[] = [
                'stage' => $eventName,
                'count' => $count,
                'dropoff_percentage' => $dropoff,
            ];

            $previousCount = $count;
        }

        return $funnelData;
    }
}
