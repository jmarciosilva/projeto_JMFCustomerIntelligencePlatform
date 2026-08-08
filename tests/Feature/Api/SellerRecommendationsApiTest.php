<?php

use App\Models\Application;
use App\Models\BusinessRecommendation;

test('seller recommendations endpoint returns recommendations for authenticated application', function () {
    $application = Application::factory()->create();
    $token = $application->createToken('test')->plainTextToken;

    BusinessRecommendation::create([
        'application_id' => $application->id,
        'seller_id' => 5,
        'type' => BusinessRecommendation::TYPE_SALES_DROP,
        'priority' => 80,
        'title' => 'Queda de vendas',
        'message' => 'test message',
        'generated_at' => now(),
    ]);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/marketplace/sellers/5/recommendations')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJson(['seller_id' => 5]);
});

test('recommendations are isolated between sellers', function () {
    $application = Application::factory()->create();
    $token = $application->createToken('test')->plainTextToken;

    BusinessRecommendation::create([
        'application_id' => $application->id, 'seller_id' => 5, 'type' => BusinessRecommendation::TYPE_SALES_DROP,
        'priority' => 80, 'title' => 'seller 5', 'message' => 'x', 'generated_at' => now(),
    ]);
    BusinessRecommendation::create([
        'application_id' => $application->id, 'seller_id' => 6, 'type' => BusinessRecommendation::TYPE_SALES_DROP,
        'priority' => 80, 'title' => 'seller 6', 'message' => 'x', 'generated_at' => now(),
    ]);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/marketplace/sellers/5/recommendations')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'seller 5');
});

test('recommendations are isolated between applications', function () {
    $app1 = Application::factory()->create();
    $app2 = Application::factory()->create();
    $token1 = $app1->createToken('test')->plainTextToken;

    BusinessRecommendation::create([
        'application_id' => $app1->id, 'seller_id' => 5, 'type' => BusinessRecommendation::TYPE_SALES_DROP,
        'priority' => 80, 'title' => 'app1', 'message' => 'x', 'generated_at' => now(),
    ]);
    BusinessRecommendation::create([
        'application_id' => $app2->id, 'seller_id' => 5, 'type' => BusinessRecommendation::TYPE_SALES_DROP,
        'priority' => 80, 'title' => 'app2', 'message' => 'x', 'generated_at' => now(),
    ]);

    $this->withHeader('Authorization', "Bearer {$token1}")
        ->getJson('/api/v1/marketplace/sellers/5/recommendations')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'app1');
});

test('recommendations are ordered by priority descending', function () {
    $application = Application::factory()->create();
    $token = $application->createToken('test')->plainTextToken;

    BusinessRecommendation::create([
        'application_id' => $application->id, 'seller_id' => 5, 'type' => BusinessRecommendation::TYPE_SALES_DROP,
        'priority' => 40, 'title' => 'low', 'message' => 'x', 'generated_at' => now(),
    ]);
    BusinessRecommendation::create([
        'application_id' => $application->id, 'seller_id' => 5, 'type' => BusinessRecommendation::TYPE_KIT_OPPORTUNITY,
        'priority' => 90, 'title' => 'high', 'message' => 'x', 'generated_at' => now(),
    ]);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/marketplace/sellers/5/recommendations')
        ->assertOk()
        ->assertJsonPath('data.0.title', 'high');
});

test('recommendations endpoint filters by type', function () {
    $application = Application::factory()->create();
    $token = $application->createToken('test')->plainTextToken;

    BusinessRecommendation::create([
        'application_id' => $application->id, 'seller_id' => 5, 'type' => BusinessRecommendation::TYPE_SALES_DROP,
        'priority' => 80, 'title' => 'drop', 'message' => 'x', 'generated_at' => now(),
    ]);
    BusinessRecommendation::create([
        'application_id' => $application->id, 'seller_id' => 5, 'type' => BusinessRecommendation::TYPE_IDEAL_TIMING,
        'priority' => 50, 'title' => 'timing', 'message' => 'x', 'generated_at' => now(),
    ]);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/marketplace/sellers/5/recommendations?type=ideal_timing')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'timing');
});

test('invalid recommendation type returns 422', function () {
    $application = Application::factory()->create();
    $token = $application->createToken('test')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/marketplace/sellers/5/recommendations?type=invalid')
        ->assertStatus(422);
});

test('unauthenticated request is rejected', function () {
    $this->getJson('/api/v1/marketplace/sellers/5/recommendations')
        ->assertUnauthorized();
});
