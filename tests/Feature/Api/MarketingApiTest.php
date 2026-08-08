<?php

use App\Models\Application;
use App\Models\MarketingContent;

test('generate endpoint creates all content types for a product', function () {
    $application = Application::factory()->create();
    $token = $application->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/marketing/generate', [
            'subject_type' => 'product',
            'subject_id' => 42,
            'product' => [
                'name' => 'Vaso de Cerâmica',
                'category' => 'Artesanato',
                'price' => 89.90,
            ],
        ]);

    $response->assertCreated();
    // 3 product content (title, description, seo) + 3 social + 1 email = 7
    expect($response->json('total'))->toBe(7);
    expect(MarketingContent::where('application_id', $application->id)->count())->toBe(7);
});

test('generate endpoint validates required fields', function () {
    $application = Application::factory()->create();
    $token = $application->createToken('test')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/marketing/generate', ['subject_type' => 'product'])
        ->assertStatus(422);
});

test('list content endpoint returns content for a subject', function () {
    $application = Application::factory()->create();
    $token = $application->createToken('test')->plainTextToken;

    MarketingContent::create([
        'application_id' => $application->id, 'subject_type' => 'product', 'subject_id' => 42,
        'type' => MarketingContent::TYPE_TITLE, 'content' => 'Title', 'status' => 'draft',
        'generator' => 'template', 'generated_at' => now(),
    ]);
    MarketingContent::create([
        'application_id' => $application->id, 'subject_type' => 'product', 'subject_id' => 99,
        'type' => MarketingContent::TYPE_TITLE, 'content' => 'Other', 'status' => 'draft',
        'generator' => 'template', 'generated_at' => now(),
    ]);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/marketing/content?subject_type=product&subject_id=42')
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

test('list content is isolated between applications', function () {
    $app1 = Application::factory()->create();
    $app2 = Application::factory()->create();
    $token1 = $app1->createToken('test')->plainTextToken;

    MarketingContent::create([
        'application_id' => $app1->id, 'subject_type' => 'product', 'subject_id' => 42,
        'type' => MarketingContent::TYPE_TITLE, 'content' => 'App1', 'status' => 'draft',
        'generator' => 'template', 'generated_at' => now(),
    ]);
    MarketingContent::create([
        'application_id' => $app2->id, 'subject_type' => 'product', 'subject_id' => 42,
        'type' => MarketingContent::TYPE_TITLE, 'content' => 'App2', 'status' => 'draft',
        'generator' => 'template', 'generated_at' => now(),
    ]);

    $this->withHeader('Authorization', "Bearer {$token1}")
        ->getJson('/api/v1/marketing/content?subject_type=product&subject_id=42')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.content', 'App1');
});

test('review endpoint approves and optionally edits content', function () {
    $application = Application::factory()->create();
    $token = $application->createToken('test')->plainTextToken;

    $content = MarketingContent::create([
        'application_id' => $application->id, 'subject_type' => 'product', 'subject_id' => 42,
        'type' => MarketingContent::TYPE_TITLE, 'content' => 'Original title', 'status' => 'draft',
        'generator' => 'template', 'generated_at' => now(),
    ]);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->patchJson("/api/v1/marketing/content/{$content->id}", [
            'status' => 'approved',
            'content' => 'Edited title',
        ])
        ->assertOk()
        ->assertJsonPath('data.status', 'approved')
        ->assertJsonPath('data.content', 'Edited title');

    $this->assertDatabaseHas('marketing_contents', [
        'id' => $content->id, 'status' => 'approved', 'content' => 'Edited title',
    ]);
});

test('review endpoint rejects content belonging to another application', function () {
    $app1 = Application::factory()->create();
    $app2 = Application::factory()->create();
    $token1 = $app1->createToken('test')->plainTextToken;

    $content = MarketingContent::create([
        'application_id' => $app2->id, 'subject_type' => 'product', 'subject_id' => 42,
        'type' => MarketingContent::TYPE_TITLE, 'content' => 'Foreign', 'status' => 'draft',
        'generator' => 'template', 'generated_at' => now(),
    ]);

    $this->withHeader('Authorization', "Bearer {$token1}")
        ->patchJson("/api/v1/marketing/content/{$content->id}", ['status' => 'approved'])
        ->assertNotFound();
});

test('unauthenticated requests are rejected', function () {
    $this->postJson('/api/v1/marketing/generate', [])->assertUnauthorized();
    $this->getJson('/api/v1/marketing/content?subject_type=product&subject_id=1')->assertUnauthorized();
});
