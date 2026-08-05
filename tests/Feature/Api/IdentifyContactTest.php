<?php

use App\Models\Application;
use App\Models\Contact;
use App\Models\Tenant;
use App\Models\Visitor;

test('identify com email cria contato e vincula o visitante', function () {
    $tenant = Tenant::factory()->create();
    $application = Application::factory()->for($tenant)->create();
    $token = $application->createToken('teste')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/contacts/identify', [
            'visitor_id' => 'visitor_001',
            'email' => 'jose@example.com',
            'name' => 'José',
        ])
        ->assertOk()
        ->assertJsonPath('email', 'jose@example.com');

    $this->assertDatabaseHas('contacts', ['tenant_id' => $tenant->id, 'email' => 'jose@example.com', 'name' => 'José']);

    $contact = Contact::query()->where('email', 'jose@example.com')->firstOrFail();

    $this->assertDatabaseHas('visitors', [
        'application_id' => $application->id,
        'visitor_id' => 'visitor_001',
        'contact_id' => $contact->id,
    ]);
});

test('sem external_id e sem email é rejeitado com 422', function () {
    $application = Application::factory()->create();
    $token = $application->createToken('teste')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/contacts/identify', ['visitor_id' => 'visitor_001'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['external_id', 'email']);
});

test('reidentificação com novos campos não apaga dados já conhecidos', function () {
    $application = Application::factory()->create();
    $token = $application->createToken('teste')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/contacts/identify', [
            'visitor_id' => 'visitor_001',
            'email' => 'jose@example.com',
            'name' => 'José',
        ])
        ->assertOk();

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/contacts/identify', [
            'visitor_id' => 'visitor_001',
            'email' => 'jose@example.com',
            'phone' => '11999998888',
        ])
        ->assertOk();

    $this->assertDatabaseHas('contacts', [
        'email' => 'jose@example.com',
        'name' => 'José',
        'phone' => '11999998888',
    ]);
});

test('consents enviados no identify são registrados', function () {
    $application = Application::factory()->create();
    $token = $application->createToken('teste')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/contacts/identify', [
            'visitor_id' => 'visitor_001',
            'email' => 'jose@example.com',
            'consents' => [
                ['purpose' => 'marketing', 'granted' => true],
            ],
        ])
        ->assertOk();

    $contactId = $response->json('contact_id');

    $this->assertDatabaseHas('contact_consents', [
        'contact_id' => $contactId,
        'purpose' => 'marketing',
        'granted' => true,
        'source_application_id' => $application->id,
    ]);
});

test('requisição sem token é rejeitada', function () {
    $this->postJson('/api/v1/contacts/identify', ['visitor_id' => 'v1', 'email' => 'a@a.com'])
        ->assertUnauthorized();
});

test('token de aplicação inativa é rejeitado', function () {
    $application = Application::factory()->inactive()->create();
    $token = $application->createToken('teste')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/contacts/identify', ['visitor_id' => 'v1', 'email' => 'a@a.com'])
        ->assertForbidden();
});

test('associação anônimo-conhecido: visitante já existente é vinculado ao identificar', function () {
    $application = Application::factory()->create();
    $token = $application->createToken('teste')->plainTextToken;

    Visitor::factory()->for($application)->create(['visitor_id' => 'visitor_existente', 'contact_id' => null]);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/contacts/identify', [
            'visitor_id' => 'visitor_existente',
            'email' => 'existente@example.com',
        ])
        ->assertOk();

    $visitor = Visitor::query()->where('application_id', $application->id)->where('visitor_id', 'visitor_existente')->firstOrFail();

    expect($visitor->contact_id)->not->toBeNull();
});
