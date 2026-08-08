<?php

use App\Models\Application;
use App\Models\Event;
use App\Models\Tenant;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->application = Application::factory()->for($this->tenant)->create();
    $this->token = $this->application->createToken('test')->plainTextToken;
});

test('get seller analytics returns correct structure', function () {
    // Create sample events for a seller
    Event::factory()->count(5)->for($this->application)->create([
        'event_name' => 'product.viewed',
        'properties' => ['seller_id' => 5, 'product_id' => 42],
    ]);

    Event::factory()->count(2)->for($this->application)->create([
        'event_name' => 'purchase.completed',
        'properties' => ['seller_id' => 5, 'total_value' => 99.90],
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson('/api/v1/marketplace/sellers/5/analytics?days=7');

    expect($response->status())->toBe(200);
    expect($response->json())->toHaveKeys(['seller_id', 'period', 'totals', 'daily', 'products', 'conversion_funnel']);
    expect($response->json('seller_id'))->toBe(5);
});

test('get top products returns products sorted by views', function () {
    // Create product views
    Event::factory()->count(10)->for($this->application)->create([
        'event_name' => 'product.viewed',
        'properties' => ['product_id' => 42, 'seller_id' => 5],
    ]);

    Event::factory()->count(5)->for($this->application)->create([
        'event_name' => 'product.viewed',
        'properties' => ['product_id' => 43, 'seller_id' => 5],
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson('/api/v1/marketplace/products/top?days=7&limit=5');

    expect($response->status())->toBe(200);
    expect($response->json())->toHaveKeys(['data', 'total', 'period']);
    expect($response->json('data.0.views'))->toBeGreaterThan($response->json('data.1.views'));
});

test('customer journey shows correct event sequence', function () {
    $contact = \App\Models\Contact::factory()->for($this->tenant)->create();

    // Create a journey sequence
    Event::factory()->for($this->application)->create([
        'contact_id' => $contact->id,
        'event_name' => 'product.viewed',
        'properties' => ['product_id' => 42],
        'occurred_at' => now()->subHours(3),
    ]);

    Event::factory()->for($this->application)->create([
        'contact_id' => $contact->id,
        'event_name' => 'cart.item_added',
        'properties' => ['product_id' => 42],
        'occurred_at' => now()->subHours(2),
    ]);

    Event::factory()->for($this->application)->create([
        'contact_id' => $contact->id,
        'event_name' => 'purchase.completed',
        'properties' => ['total_value' => 99.90],
        'occurred_at' => now()->subHours(1),
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson("/api/v1/marketplace/journey/{$contact->id}");

    expect($response->status())->toBe(200);
    expect($response->json('total_events'))->toBe(3);
    expect($response->json('journey_stages'))->toContain('product_discovery', 'cart_addition', 'purchase_completed');
});
