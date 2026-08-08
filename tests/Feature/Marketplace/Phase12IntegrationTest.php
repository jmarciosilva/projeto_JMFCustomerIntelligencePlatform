<?php

use App\Models\Application;
use App\Models\Event;
use App\Models\Tenant;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->application = Application::factory()->for($this->tenant)->create();
    $this->token = $this->application->createToken('test')->plainTextToken;
});

test('marketplace events are captured correctly', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson('/api/v1/events', [
            'event_id' => 'test-1',
            'event_name' => 'product.viewed',
            'visitor_id' => 'visitor-123',
            'occurred_at' => now()->toIso8601String(),
            'properties' => ['product_id' => 42, 'seller_id' => 5],
        ]);

    expect($response->status())->toBe(201);
    expect(Event::where('event_name', 'product.viewed')->count())->toBe(1);
});

test('purchase event contains revenue information', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson('/api/v1/events', [
            'event_id' => 'purchase-1',
            'event_name' => 'purchase.completed',
            'visitor_id' => 'visitor-456',
            'occurred_at' => now()->toIso8601String(),
            'properties' => ['total_value' => 99.90, 'order_id' => 'ORD-001'],
        ]);

    expect($response->status())->toBe(201);

    $event = Event::where('event_name', 'purchase.completed')->first();
    expect($event->properties->get('total_value'))->toBe(99.90);
});

test('cart abandonment event captured', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson('/api/v1/events', [
            'event_id' => 'cart-abandoned-1',
            'event_name' => 'cart.abandoned',
            'visitor_id' => 'visitor-789',
            'occurred_at' => now()->toIso8601String(),
            'properties' => ['items_count' => 3, 'total_value' => 150.00],
        ]);

    expect($response->status())->toBe(201);
    expect(Event::where('event_name', 'cart.abandoned')->count())->toBe(1);
});

test('review event captured with rating', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson('/api/v1/events', [
            'event_id' => 'review-1',
            'event_name' => 'review.submitted',
            'visitor_id' => 'visitor-111',
            'occurred_at' => now()->toIso8601String(),
            'properties' => ['product_id' => 42, 'rating' => 5, 'seller_id' => 5],
        ]);

    expect($response->status())->toBe(201);
    expect(Event::where('event_name', 'review.submitted')->count())->toBe(1);
});
