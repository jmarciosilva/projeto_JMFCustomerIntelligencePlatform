<?php

use App\Application\Timeline\Actions\GetContactTimelineAction;
use App\Models\Application;
use App\Models\Contact;

test('timeline do contato inclui eventos antes e depois da identificação, ordenados do mais recente', function () {
    $application = Application::factory()->create();
    $token = $application->createToken('teste')->plainTextToken;

    $olderOccurredAt = now()->subDays(2);
    $newerOccurredAt = now();

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/events', validEventPayload([
            'visitor_id' => 'visitor_timeline',
            'event_name' => 'page.viewed',
            'occurred_at' => $olderOccurredAt->toIso8601String(),
        ]))
        ->assertStatus(202);

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/contacts/identify', [
            'visitor_id' => 'visitor_timeline',
            'email' => 'timeline@example.com',
        ])
        ->assertOk();

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/events', validEventPayload([
            'visitor_id' => 'visitor_timeline',
            'event_name' => 'article.viewed',
            'occurred_at' => $newerOccurredAt->toIso8601String(),
        ]))
        ->assertStatus(202);

    $contact = Contact::query()->findOrFail($response->json('contact_id'));
    $timeline = app(GetContactTimelineAction::class)->handle($contact);

    expect($timeline->total())->toBe(2);

    $names = collect($timeline->items())->pluck('event_name')->all();
    expect($names)->toBe(['article.viewed', 'page.viewed']);
});

test('timeline de um contato não inclui eventos de outro visitante/contato', function () {
    $application = Application::factory()->create();
    $token = $application->createToken('teste')->plainTextToken;

    $responseA = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/contacts/identify', ['visitor_id' => 'visitor_a', 'email' => 'a@example.com'])
        ->assertOk();

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/contacts/identify', ['visitor_id' => 'visitor_b', 'email' => 'b@example.com'])
        ->assertOk();

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/events', validEventPayload(['visitor_id' => 'visitor_a', 'event_name' => 'article.viewed']))
        ->assertStatus(202);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/events', validEventPayload(['visitor_id' => 'visitor_b', 'event_name' => 'service.viewed']))
        ->assertStatus(202);

    $contactA = Contact::query()->findOrFail($responseA->json('contact_id'));
    $timeline = app(GetContactTimelineAction::class)->handle($contactA);

    expect($timeline->total())->toBe(1);
    expect($timeline->items()[0]->event_name)->toBe('article.viewed');
});
