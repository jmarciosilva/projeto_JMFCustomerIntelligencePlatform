<?php

use App\Models\Application;
use App\Models\Opportunity;

test('cross-sell endpoint returns opportunities for authenticated application', function () {
    $application = Application::factory()->create();
    $token = $application->createToken('test')->plainTextToken;

    Opportunity::create([
        'application_id' => $application->id,
        'type' => Opportunity::TYPE_CROSS_SELL,
        'product_id' => 10,
        'related_product_id' => 20,
        'score' => 80,
        'reason' => 'test',
        'detected_at' => now(),
    ]);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/opportunities/cross-sell')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJson(['type' => 'cross-sell']);
});

test('opportunities are isolated between applications', function () {
    $app1 = Application::factory()->create();
    $app2 = Application::factory()->create();
    $token1 = $app1->createToken('test')->plainTextToken;

    Opportunity::create([
        'application_id' => $app1->id, 'type' => Opportunity::TYPE_UP_SELL,
        'score' => 70, 'reason' => 'app1', 'detected_at' => now(),
    ]);
    Opportunity::create([
        'application_id' => $app2->id, 'type' => Opportunity::TYPE_UP_SELL,
        'score' => 70, 'reason' => 'app2', 'detected_at' => now(),
    ]);

    $this->withHeader('Authorization', "Bearer {$token1}")
        ->getJson('/api/v1/opportunities/up-sell')
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

test('invalid opportunity type returns 404', function () {
    $application = Application::factory()->create();
    $token = $application->createToken('test')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/opportunities/invalid-type')
        ->assertNotFound();
});

test('unauthenticated request is rejected', function () {
    $this->getJson('/api/v1/opportunities/cross-sell')
        ->assertUnauthorized();
});

test('cross-sell endpoint filters by product_id', function () {
    $application = Application::factory()->create();
    $token = $application->createToken('test')->plainTextToken;

    Opportunity::create([
        'application_id' => $application->id, 'type' => Opportunity::TYPE_CROSS_SELL,
        'product_id' => 10, 'related_product_id' => 20, 'score' => 80,
        'reason' => 'match', 'detected_at' => now(),
    ]);
    Opportunity::create([
        'application_id' => $application->id, 'type' => Opportunity::TYPE_CROSS_SELL,
        'product_id' => 99, 'related_product_id' => 98, 'score' => 80,
        'reason' => 'no match', 'detected_at' => now(),
    ]);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/opportunities/cross-sell?product_id=10')
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

test('opportunities are ordered by score descending', function () {
    $application = Application::factory()->create();
    $token = $application->createToken('test')->plainTextToken;

    Opportunity::create([
        'application_id' => $application->id, 'type' => Opportunity::TYPE_BUNDLE,
        'score' => 50, 'reason' => 'low', 'detected_at' => now(),
    ]);
    Opportunity::create([
        'application_id' => $application->id, 'type' => Opportunity::TYPE_BUNDLE,
        'score' => 90, 'reason' => 'high', 'detected_at' => now(),
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/opportunities/bundles')
        ->assertOk();

    expect($response->json('data.0.reason'))->toBe('high');
});
