<?php

use App\Models\Application;
use App\Models\Contact;
use App\Models\Event;

test('token válido retorna detalhe do contato', function () {
    $application = Application::factory()->create();
    $token = $application->createToken('teste')->plainTextToken;
    $contact = Contact::factory()->create(['tenant_id' => $application->tenant_id, 'email' => 'cliente@teste.com']);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson("/api/v1/contacts/{$contact->id}")
        ->assertOk()
        ->assertJson(['id' => $contact->id, 'email' => 'cliente@teste.com']);
});

test('requisição sem token é rejeitada', function () {
    $application = Application::factory()->create();
    $contact = Contact::factory()->create(['tenant_id' => $application->tenant_id]);

    $this->getJson("/api/v1/contacts/{$contact->id}")->assertUnauthorized();
});

test('contato de outro tenant retorna 404', function () {
    $applicationA = Application::factory()->create();
    $applicationB = Application::factory()->create();
    $tokenA = $applicationA->createToken('a')->plainTextToken;

    $contactFromB = Contact::factory()->create(['tenant_id' => $applicationB->tenant_id]);

    $this->withHeader('Authorization', "Bearer {$tokenA}")
        ->getJson("/api/v1/contacts/{$contactFromB->id}")
        ->assertNotFound();
});

test('eventos do contato são retornados e escopados pela aplicação', function () {
    $applicationA = Application::factory()->create();
    $applicationB = Application::factory()->create(['tenant_id' => $applicationA->tenant_id]);
    $tokenA = $applicationA->createToken('a')->plainTextToken;
    $contact = Contact::factory()->create(['tenant_id' => $applicationA->tenant_id]);

    Event::factory()->for($applicationA)->create(['contact_id' => $contact->id, 'event_name' => 'produto.visualizado']);
    Event::factory()->for($applicationB)->create(['contact_id' => $contact->id, 'event_name' => 'outro.evento']);

    $this->withHeader('Authorization', "Bearer {$tokenA}")
        ->getJson("/api/v1/contacts/{$contact->id}/events")
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJson(['data' => [['event_name' => 'produto.visualizado']]]);
});
