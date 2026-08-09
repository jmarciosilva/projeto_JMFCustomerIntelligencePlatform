<?php

use App\Events\EventWasIngested;
use App\Models\Application;
use App\Models\Contact;
use App\Models\Event;
use App\Models\Tenant;

// Sanctum's RequestGuard caches the resolved user for its lifetime; within a single
// test method, sequential requests reuse that cached guard unless explicitly reset.
// Production is unaffected (fresh PHP process per request), but tests issuing two
// different tokens must call auth()->forgetGuards() between calls.

test('seller analytics does not leak data between applications', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    $app1 = Application::factory()->for($tenant1)->create();
    $app2 = Application::factory()->for($tenant2)->create();

    $token1 = $app1->createToken('test')->plainTextToken;
    $token2 = $app2->createToken('test')->plainTextToken;

    // Create events in app1 (materializing MarketplaceMetric, as production does via EventWasIngested)
    Event::factory()->count(10)->for($app1)->create([
        'event_name' => 'product.viewed',
        'properties' => ['seller_id' => 5, 'product_id' => 42],
    ])->each(fn ($event) => event(new EventWasIngested($event)));

    // Create events in app2
    Event::factory()->count(5)->for($app2)->create([
        'event_name' => 'product.viewed',
        'properties' => ['seller_id' => 5, 'product_id' => 43],
    ])->each(fn ($event) => event(new EventWasIngested($event)));

    $response1 = $this->withHeader('Authorization', "Bearer {$token1}")
        ->getJson('/api/v1/marketplace/sellers/5/analytics');

    auth()->forgetGuards();

    $response2 = $this->withHeader('Authorization', "Bearer {$token2}")
        ->getJson('/api/v1/marketplace/sellers/5/analytics');

    expect($response1->status())->toBe(200);
    expect($response2->status())->toBe(200);

    // Verify data isolation - they should have different totals
    $app1Totals = $response1->json('totals');
    $app2Totals = $response2->json('totals');

    expect($app1Totals['product_views'])->toBeGreaterThan($app2Totals['product_views']);
});

test('top products only shows data from current application', function () {
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();

    $app1 = Application::factory()->for($tenant1)->create();
    $app2 = Application::factory()->for($tenant2)->create();

    $token1 = $app1->createToken('test')->plainTextToken;
    $token2 = $app2->createToken('test')->plainTextToken;

    // App1: Product 42 has 10 views
    Event::factory()->count(10)->for($app1)->create([
        'event_name' => 'product.viewed',
        'properties' => ['product_id' => 42],
    ]);

    // App2: Product 42 has 5 views
    Event::factory()->count(5)->for($app2)->create([
        'event_name' => 'product.viewed',
        'properties' => ['product_id' => 42],
    ]);

    $response1 = $this->withHeader('Authorization', "Bearer {$token1}")
        ->getJson('/api/v1/marketplace/products/top');
    $views1 = $response1->json('data.0.views') ?? 0;

    auth()->forgetGuards();

    $response2 = $this->withHeader('Authorization', "Bearer {$token2}")
        ->getJson('/api/v1/marketplace/products/top');
    $views2 = $response2->json('data.0.views') ?? 0;

    expect($views1)->toBe(10);
    expect($views2)->toBe(5);
});

test('customer journey shows only authenticated application data', function () {
    $tenant = Tenant::factory()->create();
    $app1 = Application::factory()->for($tenant)->create();
    $app2 = Application::factory()->for($tenant)->create();

    $token1 = $app1->createToken('test')->plainTextToken;

    $contact = Contact::factory()->for($tenant)->create();

    // Create events in app1
    Event::factory()->for($app1)->create([
        'contact_id' => $contact->id,
        'event_name' => 'product.viewed',
    ]);

    Event::factory()->for($app1)->create([
        'contact_id' => $contact->id,
        'event_name' => 'purchase.completed',
    ]);

    // Create events in app2
    Event::factory()->for($app2)->create([
        'contact_id' => $contact->id,
        'event_name' => 'product.viewed',
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token1}")
        ->getJson("/api/v1/marketplace/journey/{$contact->id}");

    expect($response->status())->toBe(200);
    expect($response->json('total_events'))->toBe(2);
});
