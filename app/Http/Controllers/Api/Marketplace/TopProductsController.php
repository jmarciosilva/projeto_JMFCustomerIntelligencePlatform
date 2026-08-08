<?php

namespace App\Http\Controllers\Api\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TopProductsController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $days = $request->get('days', 7);
        $limit = $request->get('limit', 10);
        $startDate = now()->subDays($days)->startOfDay();
        $endDate = now()->endOfDay();

        $topProducts = Event::where('application_id', $request->user()->application->id)
            ->whereIn('event_name', ['product.viewed', 'purchase.completed'])
            ->whereBetween('occurred_at', [$startDate, $endDate])
            ->get()
            ->groupBy('properties.product_id')
            ->map(function ($events, $productId) {
                return [
                    'product_id' => $productId,
                    'views' => $events->where('event_name', 'product.viewed')->count(),
                    'purchases' => $events->where('event_name', 'purchase.completed')->count(),
                    'conversion_rate' => $this->calculateConversionRate($events),
                ];
            })
            ->sortByDesc('views')
            ->take($limit)
            ->values()
            ->toArray();

        return response()->json([
            'data' => $topProducts,
            'total' => count($topProducts),
            'period' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
                'days' => $days,
            ],
        ]);
    }

    private function calculateConversionRate($events): float
    {
        $views = $events->where('event_name', 'product.viewed')->count();
        $purchases = $events->where('event_name', 'purchase.completed')->count();

        return $views > 0 ? round(($purchases / $views) * 100, 2) : 0;
    }
}
