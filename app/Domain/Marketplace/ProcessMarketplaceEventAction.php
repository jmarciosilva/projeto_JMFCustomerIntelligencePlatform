<?php

namespace App\Domain\Marketplace;

use App\Models\Event;
use App\Models\MarketplaceMetric;
use Carbon\Carbon;

class ProcessMarketplaceEventAction
{
    public function handle(Event $event): void
    {
        if (!$this->isMarketplaceEvent($event->event_name)) {
            return;
        }

        $eventDate = $event->occurred_at->toDateString();
        $sellerId = $this->extractSellerId($event);
        $productId = $this->extractProductId($event);

        if (!$sellerId) {
            return;
        }

        $metric = MarketplaceMetric::updateOrCreate(
            [
                'tenant_id' => $event->tenant_id,
                'application_id' => $event->application_id,
                'seller_id' => $sellerId,
                'product_id' => $productId,
                'date' => $eventDate,
            ],
            $this->getMetricsUpdate($event)
        );
    }

    private function isMarketplaceEvent(string $eventName): bool
    {
        $marketplaceEvents = [
            'product.viewed',
            'product.search',
            'product.filtered',
            'product.favorited',
            'product.unfavorited',
            'cart.item_added',
            'cart.item_removed',
            'cart.viewed',
            'cart.abandoned',
            'checkout.started',
            'purchase.completed',
            'purchase.cancelled',
            'review.submitted',
            'seller.contacted',
            'seller.profile_viewed',
        ];

        return in_array($eventName, $marketplaceEvents);
    }

    private function extractSellerId(Event $event): ?int
    {
        return $event->properties?->get('seller_id');
    }

    private function extractProductId(Event $event): ?int
    {
        return $event->properties?->get('product_id');
    }

    private function getMetricsUpdate(Event $event): array
    {
        $updates = [];

        match ($event->event_name) {
            'product.viewed' => $updates['product_views'] = \DB::raw('product_views + 1'),
            'product.favorited' => $updates['product_favorites'] = \DB::raw('product_favorites + 1'),
            'product.unfavorited' => $updates['product_favorites'] = \DB::raw('GREATEST(product_favorites - 1, 0)'),
            'cart.item_added' => $updates['cart_adds'] = \DB::raw('cart_adds + 1'),
            'cart.item_removed' => $updates['cart_adds'] = \DB::raw('GREATEST(cart_adds - 1, 0)'),
            'cart.viewed' => $updates['cart_views'] = \DB::raw('cart_views + 1'),
            'cart.abandoned' => $updates['cart_abandonments'] = \DB::raw('cart_abandonments + 1'),
            'checkout.started' => $updates['checkout_starts'] = \DB::raw('checkout_starts + 1'),
            'purchase.completed' => [
                $updates['purchases'] = \DB::raw('purchases + 1'),
                $updates['revenue'] = \DB::raw('revenue + ' . ($event->properties?->get('total_value') ?? 0)),
            ],
            'review.submitted' => $updates['reviews'] = \DB::raw('reviews + 1'),
            default => null,
        };

        return $updates;
    }
}
