<?php

use App\Models\Application;
use App\Models\Tenant;

test('debug event ingestion error', function () {
    $tenant = Tenant::factory()->create();
    $app = Application::factory()->for($tenant)->create();
    $token = $app->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/events', [
            'event_id' => 'test-1',
            'event_name' => 'product.viewed',
            'visitor_id' => 'visitor-123',
            'occurred_at' => now()->toIso8601String(),
            'properties' => ['product_id' => 42],
        ]);

    dd([
        'status' => $response->status(),
        'body' => $response->json(),
    ]);
});
