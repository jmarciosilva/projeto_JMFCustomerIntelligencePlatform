<?php

namespace App\Domain\Intelligence;

use App\Models\BusinessRecommendation;
use App\Models\Event;
use App\Models\MarketplaceMetric;
use App\Models\Opportunity;
use Illuminate\Support\Collection;

class BusinessAdvisor
{
    private const SALES_DROP_THRESHOLD = -20.0;

    private const PRICE_OUTLIER_THRESHOLD = 30.0; // % deviation from category average

    private const MIN_PURCHASES_FOR_TIMING = 5;

    public function generateForApplication(int $applicationId): Collection
    {
        return $this->detectSalesDrops($applicationId)
            ->merge($this->detectKitOpportunities($applicationId))
            ->merge($this->detectPriceOutliers($applicationId))
            ->merge($this->detectIdealTiming($applicationId));
    }

    public function detectSalesDrops(int $applicationId): Collection
    {
        $now = now();
        $currentStart = $now->copy()->subDays(7)->startOfDay()->toDateString();
        $currentEnd = $now->copy()->endOfDay()->toDateString();
        $previousStart = $now->copy()->subDays(14)->startOfDay()->toDateString();
        $previousEnd = $now->copy()->subDays(7)->subSecond()->endOfDay()->toDateString();

        $metrics = MarketplaceMetric::where('application_id', $applicationId)
            ->whereNotNull('seller_id')
            ->whereNotNull('product_id')
            ->whereBetween('date', [$previousStart, $currentEnd])
            ->get();

        return $metrics
            ->groupBy(fn ($m) => "{$m->seller_id}:{$m->product_id}")
            ->map(function ($rows) use ($currentStart, $currentEnd, $previousStart, $previousEnd) {
                $current = $rows->filter(fn ($m) => $m->date->toDateString() >= $currentStart && $m->date->toDateString() <= $currentEnd);
                $previous = $rows->filter(fn ($m) => $m->date->toDateString() >= $previousStart && $m->date->toDateString() <= $previousEnd);

                $currentPurchases = (int) $current->sum('purchases');
                $previousPurchases = (int) $previous->sum('purchases');

                if ($previousPurchases === 0) {
                    return null;
                }

                $growthRate = round((($currentPurchases - $previousPurchases) / $previousPurchases) * 100, 2);

                if ($growthRate > self::SALES_DROP_THRESHOLD) {
                    return null;
                }

                $sellerId = (int) $rows->first()->seller_id;
                $productId = (int) $rows->first()->product_id;

                return [
                    'seller_id' => $sellerId,
                    'type' => BusinessRecommendation::TYPE_SALES_DROP,
                    'priority' => min(100, abs($growthRate)),
                    'title' => "Queda de vendas no produto #{$productId}",
                    'message' => "Suas vendas do produto #{$productId} caíram ".abs($growthRate)."% na última semana (de {$previousPurchases} para {$currentPurchases} compras). Considere revisar preço, fotos ou fazer uma promoção.",
                    'data' => [
                        'product_id' => $productId,
                        'current_purchases' => $currentPurchases,
                        'previous_purchases' => $previousPurchases,
                        'growth_rate' => $growthRate,
                    ],
                ];
            })
            ->filter()
            ->values();
    }

    public function detectKitOpportunities(int $applicationId): Collection
    {
        return Opportunity::where('application_id', $applicationId)
            ->where('type', Opportunity::TYPE_BUNDLE)
            ->get()
            ->map(function (Opportunity $opportunity) use ($applicationId) {
                $sellerId = $this->findSellerForProduct($applicationId, $opportunity->product_id);

                if (! $sellerId) {
                    return null;
                }

                return [
                    'seller_id' => $sellerId,
                    'type' => BusinessRecommendation::TYPE_KIT_OPPORTUNITY,
                    'priority' => (float) $opportunity->score,
                    'title' => 'Oportunidade de kit promocional',
                    'message' => "Clientes que se interessam pelo produto #{$opportunity->product_id} também compram o produto #{$opportunity->related_product_id}. Considere criar um kit promocional com os dois.",
                    'data' => [
                        'product_id' => $opportunity->product_id,
                        'related_product_id' => $opportunity->related_product_id,
                    ],
                ];
            })
            ->filter()
            ->values();
    }

    public function detectPriceOutliers(int $applicationId): Collection
    {
        $views = Event::where('application_id', $applicationId)
            ->where('event_name', 'product.viewed')
            ->orderByDesc('occurred_at')
            ->get()
            ->filter(fn ($e) => isset($e->properties['category'], $e->properties['price'], $e->properties['product_id']));

        if ($views->isEmpty()) {
            return collect();
        }

        $categoryAverages = $views->groupBy(fn ($e) => $e->properties['category'])
            ->map(fn ($group) => $group->avg(fn ($e) => (float) $e->properties['price']));

        $latestPerProduct = $views->groupBy(fn ($e) => $e->properties['product_id'])
            ->map(fn ($group) => $group->first());

        return $latestPerProduct->map(function ($event) use ($categoryAverages) {
            $category = $event->properties['category'];
            $price = (float) $event->properties['price'];
            $productId = $event->properties['product_id'];
            $sellerId = $event->properties['seller_id'] ?? null;
            $categoryAvg = $categoryAverages->get($category);

            if (! $sellerId || ! $categoryAvg || $categoryAvg <= 0) {
                return null;
            }

            $deviation = round((($price - $categoryAvg) / $categoryAvg) * 100, 2);

            if (abs($deviation) < self::PRICE_OUTLIER_THRESHOLD) {
                return null;
            }

            $direction = $deviation > 0 ? 'acima' : 'abaixo';
            $riskMessage = $deviation > 0
                ? 'pode estar afastando compradores sensíveis a preço'
                : 'pode estar deixando dinheiro na mesa — considere reajustar';

            return [
                'seller_id' => (int) $sellerId,
                'type' => BusinessRecommendation::TYPE_PRICE_OUTLIER,
                'priority' => min(100, abs($deviation)),
                'title' => "Preço fora da média em \"{$category}\"",
                'message' => "O preço do produto #{$productId} está ".abs($deviation)."% {$direction} da média da categoria \"{$category}\" (R$ ".number_format($price, 2, ',', '.').' vs média R$ '.number_format($categoryAvg, 2, ',', '.').") — {$riskMessage}.",
                'data' => [
                    'product_id' => $productId,
                    'category' => $category,
                    'price' => $price,
                    'category_average' => round($categoryAvg, 2),
                    'deviation' => $deviation,
                ],
            ];
        })
            ->filter()
            ->values();
    }

    public function detectIdealTiming(int $applicationId): Collection
    {
        $purchases = Event::where('application_id', $applicationId)
            ->where('event_name', 'purchase.completed')
            ->get()
            ->filter(fn ($e) => ! empty($e->properties['seller_id']));

        return $purchases->groupBy(fn ($e) => $e->properties['seller_id'])
            ->filter(fn ($group) => $group->count() >= self::MIN_PURCHASES_FOR_TIMING)
            ->map(function ($group, $sellerId) {
                $peakHour = $group->groupBy(fn ($e) => $e->occurred_at->format('G'))
                    ->sortByDesc(fn ($g) => $g->count())
                    ->keys()
                    ->first();

                return [
                    'seller_id' => (int) $sellerId,
                    'type' => BusinessRecommendation::TYPE_IDEAL_TIMING,
                    'priority' => 50.0,
                    'title' => 'Melhor horário para vender',
                    'message' => "Seus melhores horários de venda são por volta das {$peakHour}h. Considere publicar novos produtos ou promoções nesse horário.",
                    'data' => [
                        'peak_hour' => (int) $peakHour,
                        'total_purchases_analyzed' => $group->count(),
                    ],
                ];
            })
            ->values();
    }

    private function findSellerForProduct(int $applicationId, int|string|null $productId): ?int
    {
        if (! $productId) {
            return null;
        }

        $metric = MarketplaceMetric::where('application_id', $applicationId)
            ->where('product_id', $productId)
            ->whereNotNull('seller_id')
            ->orderByDesc('date')
            ->first();

        return $metric?->seller_id;
    }
}
