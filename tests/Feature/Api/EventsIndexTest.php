<?php

use App\Models\Application;
use App\Models\Contact;
use App\Models\Event;

test('token válido retorna eventos no formato esperado pelo SDK', function () {
    $application = Application::factory()->create();
    $token = $application->createToken('teste')->plainTextToken;
    $contact = Contact::factory()->create(['tenant_id' => $application->tenant_id, 'email' => 'cliente@teste.com']);

    Event::factory()->for($application)->create([
        'event_name' => 'produto.visualizado',
        'contact_id' => $contact->id,
    ]);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/events')
        ->assertOk()
        ->assertJsonStructure(['data', 'total', 'per_page', 'current_page'])
        ->assertJson([
            'total' => 1,
            'data' => [
                ['event_name' => 'produto.visualizado', 'contact_email' => 'cliente@teste.com'],
            ],
        ]);
});

test('requisição sem token é rejeitada', function () {
    $this->getJson('/api/v1/events')->assertUnauthorized();
});

test('eventos são isolados por aplicação', function () {
    $applicationA = Application::factory()->create();
    $applicationB = Application::factory()->create();
    $tokenA = $applicationA->createToken('a')->plainTextToken;

    Event::factory()->for($applicationA)->create();
    Event::factory()->for($applicationB)->count(3)->create();

    $this->withHeader('Authorization', "Bearer {$tokenA}")
        ->getJson('/api/v1/events')
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

test('filtro event_name funciona', function () {
    $application = Application::factory()->create();
    $token = $application->createToken('teste')->plainTextToken;

    Event::factory()->for($application)->create(['event_name' => 'produto.visualizado']);
    Event::factory()->for($application)->create(['event_name' => 'pedido.criado']);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/events?event_name=produto')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJson(['data' => [['event_name' => 'produto.visualizado']]]);
});
