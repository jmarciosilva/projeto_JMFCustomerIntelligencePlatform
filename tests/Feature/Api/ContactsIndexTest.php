<?php

use App\Models\Application;
use App\Models\Contact;
use App\Models\Event;

test('token válido retorna contatos no formato esperado pelo SDK', function () {
    $application = Application::factory()->create();
    $token = $application->createToken('teste')->plainTextToken;

    Contact::factory()->create([
        'tenant_id' => $application->tenant_id,
        'email' => 'cliente@teste.com',
        'name' => 'Cliente Teste',
    ]);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/contacts')
        ->assertOk()
        ->assertJsonStructure(['data', 'total', 'per_page', 'current_page'])
        ->assertJson([
            'total' => 1,
            'data' => [
                ['email' => 'cliente@teste.com', 'name' => 'Cliente Teste'],
            ],
        ]);
});

test('requisição sem token é rejeitada', function () {
    $this->getJson('/api/v1/contacts')->assertUnauthorized();
});

test('contatos são isolados por tenant', function () {
    $applicationA = Application::factory()->create();
    $applicationB = Application::factory()->create();
    $tokenA = $applicationA->createToken('a')->plainTextToken;

    Contact::factory()->create(['tenant_id' => $applicationA->tenant_id]);
    Contact::factory()->count(2)->create(['tenant_id' => $applicationB->tenant_id]);

    $this->withHeader('Authorization', "Bearer {$tokenA}")
        ->getJson('/api/v1/contacts')
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

test('busca por email filtra contatos', function () {
    $application = Application::factory()->create();
    $token = $application->createToken('teste')->plainTextToken;

    Contact::factory()->create(['tenant_id' => $application->tenant_id, 'email' => 'joao@teste.com']);
    Contact::factory()->create(['tenant_id' => $application->tenant_id, 'email' => 'maria@teste.com']);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/contacts?search=joao')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJson(['data' => [['email' => 'joao@teste.com']]]);
});

test('last_event_at reflete o evento mais recente do contato', function () {
    $application = Application::factory()->create();
    $token = $application->createToken('teste')->plainTextToken;
    $contact = Contact::factory()->create(['tenant_id' => $application->tenant_id]);

    Event::factory()->for($application)->create([
        'contact_id' => $contact->id,
        'occurred_at' => now()->subDays(5),
    ]);
    Event::factory()->for($application)->create([
        'contact_id' => $contact->id,
        'occurred_at' => now(),
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/contacts')
        ->assertOk();

    expect($response->json('data.0.last_event_at'))->not->toBeNull();
});
