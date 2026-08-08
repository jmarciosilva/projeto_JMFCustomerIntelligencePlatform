<?php

use App\Models\Application;
use App\Models\Event;
use App\Models\MarketplaceMetric;
use App\Models\Tenant;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->application = Application::factory()->for($this->tenant)->create();
    $this->token = $this->application->createToken('test')->plainTextToken;
});

test('captures product viewed event', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson('/api/v1/events', [
        'event_id' => 'test-product-viewed-1',
        'event_name' => 'product.viewed',
        'visitor_id' => 'visitor-123',
        'occurred_at' => now()->toIso8601String(),
        'properties' => [
            'product_id' => 42,
            'seller_id' => 5,
            'category' => 'Artesanato',
            'price' => 49.90,
        ],
        'context' => [
            'page_url' => '/produtos/42',
            'referrer' => 'https://google.com',
        ],
    ]);

    expect($response->status())->toBe(201);
    expect(Event::where('event_name', 'product.viewed')->count())->toBe(1);
});

test('captures cart abandoned event', function () {
    // Using token authentication

    $response = $this->withHeader("Authorization", "Bearer {$this->token}")->postJson('/api/v1/events', [
        'event_id' => 'test-cart-abandoned-1',
        'event_name' => 'cart.abandoned',
        'visitor_id' => 'visitor-123',
        'occurred_at' => now()->toIso8601String(),
        'properties' => [
            'items_count' => 3,
            'total_value' => 149.70,
            'sellers_involved' => [5, 8],
            'time_to_abandon' => 1800,
        ],
    ]);

    expect($response->status())->toBe(201);
    expect(Event::where('event_name', 'cart.abandoned')->count())->toBe(1);
});

test('captures purchase completed event', function () {
    // Using token authentication

    $response = $this->withHeader("Authorization", "Bearer {$this->token}")->postJson('/api/v1/events', [
        'event_id' => 'test-purchase-1',
        'event_name' => 'purchase.completed',
        'visitor_id' => 'visitor-123',
        'occurred_at' => now()->toIso8601String(),
        'properties' => [
            'order_id' => 'ORD-2026-0001',
            'items_count' => 3,
            'total_value' => 149.70,
            'sellers' => [
                ['seller_id' => 5, 'items_count' => 1, 'subtotal' => 49.90],
                ['seller_id' => 8, 'items_count' => 2, 'subtotal' => 99.80],
            ],
            'payment_method' => 'credit_card',
        ],
    ]);

    expect($response->status())->toBe(201);
    expect(Event::where('event_name', 'purchase.completed')->count())->toBe(1);
});

test('captures review submitted event', function () {
    // Using token authentication

    $response = $this->withHeader("Authorization", "Bearer {$this->token}")->postJson('/api/v1/events', [
        'event_id' => 'test-review-1',
        'event_name' => 'review.submitted',
        'visitor_id' => 'visitor-123',
        'occurred_at' => now()->toIso8601String(),
        'properties' => [
            'product_id' => 42,
            'seller_id' => 5,
            'rating' => 5,
            'review_text' => 'Produto excelente!',
        ],
    ]);

    expect($response->status())->toBe(201);
    expect(Event::where('event_name', 'review.submitted')->count())->toBe(1);
});
