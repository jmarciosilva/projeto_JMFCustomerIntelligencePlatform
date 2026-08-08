<?php

use App\Models\Application;
use App\Models\Event;

test('token válido retorna métricas no formato esperado pelo SDK', function () {
    $application = Application::factory()->create();
    $token = $application->createToken('teste')->plainTextToken;

    Event::factory()->for($application)->count(3)->create([
        'occurred_at' => now(),
        'visitor_id' => 'visitor_1',
    ]);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/metrics')
        ->assertOk()
        ->assertJsonStructure(['events', 'visitors', 'sessions', 'conversions', 'trend']);
});

test('requisição sem token é rejeitada', function () {
    $this->getJson('/api/v1/metrics')->assertUnauthorized();
});

test('métricas contam apenas eventos da aplicação autenticada', function () {
    $applicationA = Application::factory()->create();
    $applicationB = Application::factory()->create();
    $tokenA = $applicationA->createToken('a')->plainTextToken;

    Event::factory()->for($applicationA)->count(2)->create(['occurred_at' => now()]);
    Event::factory()->for($applicationB)->count(5)->create(['occurred_at' => now()]);

    $this->withHeader('Authorization', "Bearer {$tokenA}")
        ->getJson('/api/v1/metrics')
        ->assertOk()
        ->assertJson(['events' => 2]);
});

test('start_date e end_date filtram o período', function () {
    $application = Application::factory()->create();
    $token = $application->createToken('teste')->plainTextToken;

    Event::factory()->for($application)->create(['occurred_at' => now()->subDays(20)]);
    Event::factory()->for($application)->create(['occurred_at' => now()]);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/metrics?start_date='.now()->subDay()->toDateString().'&end_date='.now()->toDateString())
        ->assertOk()
        ->assertJson(['events' => 1]);
});
