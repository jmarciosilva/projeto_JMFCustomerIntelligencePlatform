<?php

use App\Models\Application;
use App\Models\Event;
use App\Models\Tenant;
use App\Models\Visitor;

test('token válido com payload válido é aceito e persistido de forma assíncrona', function () {
    $tenant = Tenant::factory()->create();
    $application = Application::factory()->for($tenant)->create();
    $token = $application->createToken('teste')->plainTextToken;

    $payload = validEventPayload();

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/events', $payload)
        ->assertStatus(202)
        ->assertJson(['status' => 'accepted', 'event_id' => $payload['event_id']]);

    $this->assertDatabaseHas('events', [
        'application_id' => $application->id,
        'tenant_id' => $tenant->id,
        'event_id' => $payload['event_id'],
        'event_name' => 'article.viewed',
        'visitor_id' => 'visitor_001',
    ]);
});

test('requisição sem token é rejeitada', function () {
    $this->postJson('/api/v1/events', validEventPayload())->assertUnauthorized();
});

test('token de aplicação inativa é rejeitado', function () {
    $application = Application::factory()->inactive()->create();
    $token = $application->createToken('teste')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/events', validEventPayload())
        ->assertForbidden();
});

test('token de tenant inativo é rejeitado', function () {
    $tenant = Tenant::factory()->inactive()->create();
    $application = Application::factory()->for($tenant)->create();
    $token = $application->createToken('teste')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/events', validEventPayload())
        ->assertForbidden();
});

test('payload sem campos obrigatórios é rejeitado com 422', function () {
    $application = Application::factory()->create();
    $token = $application->createToken('teste')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/events', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['event_id', 'event_name', 'visitor_id', 'occurred_at']);
});

test('event_name fora do padrão entidade.acao é rejeitado', function () {
    $application = Application::factory()->create();
    $token = $application->createToken('teste')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/events', validEventPayload(['event_name' => 'ArticleViewed']))
        ->assertStatus(422)
        ->assertJsonValidationErrors('event_name');
});

test('properties acima do tamanho máximo permitido é rejeitado com 422', function () {
    $application = Application::factory()->create();
    $token = $application->createToken('teste')->plainTextToken;

    $payload = validEventPayload(['properties' => ['blob' => str_repeat('a', 11000)]]);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/events', $payload)
        ->assertStatus(422)
        ->assertJsonValidationErrors('properties');
});

test('reenvio do mesmo event_id pela mesma aplicação é tratado como duplicado', function () {
    $application = Application::factory()->create();
    $token = $application->createToken('teste')->plainTextToken;
    $payload = validEventPayload();

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/events', $payload)
        ->assertStatus(202);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/events', $payload)
        ->assertStatus(200)
        ->assertJson(['status' => 'duplicate', 'event_id' => $payload['event_id']]);

    expect(Event::query()->where('application_id', $application->id)->where('event_id', $payload['event_id'])->count())
        ->toBe(1);
});

test('mesmo event_id em aplicações diferentes não é tratado como duplicado', function () {
    $applicationA = Application::factory()->create();
    $applicationB = Application::factory()->create();
    $tokenA = $applicationA->createToken('a')->plainTextToken;
    $tokenB = $applicationB->createToken('b')->plainTextToken;

    $payload = validEventPayload();

    $this->withHeader('Authorization', "Bearer {$tokenA}")
        ->postJson('/api/v1/events', $payload)
        ->assertStatus(202);

    // O guard 'sanctum' cacheia o usuário resolvido na própria instância; sem
    // isso, a segunda chamada reaproveitaria a aplicação já resolvida na
    // primeira (ver mesmo comentário em ApplicationAuthTest).
    $this->app['auth']->forgetGuards();

    $this->withHeader('Authorization', "Bearer {$tokenB}")
        ->postJson('/api/v1/events', $payload)
        ->assertStatus(202);

    $this->assertDatabaseHas('events', ['application_id' => $applicationA->id, 'event_id' => $payload['event_id']]);
    $this->assertDatabaseHas('events', ['application_id' => $applicationB->id, 'event_id' => $payload['event_id']]);
});

test('processar um evento cria o visitante e a sessão correspondentes', function () {
    $application = Application::factory()->create();
    $token = $application->createToken('teste')->plainTextToken;

    $payload = validEventPayload(['visitor_id' => 'visitor_xyz', 'session_id' => 'session_xyz']);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/events', $payload)
        ->assertStatus(202);

    $this->assertDatabaseHas('visitors', [
        'application_id' => $application->id,
        'visitor_id' => 'visitor_xyz',
        'contact_id' => null,
    ]);

    $this->assertDatabaseHas('visitor_sessions', [
        'application_id' => $application->id,
        'session_id' => 'session_xyz',
    ]);
});

test('segundo evento do mesmo visitante atualiza last_seen_at em vez de duplicar', function () {
    $application = Application::factory()->create();
    $token = $application->createToken('teste')->plainTextToken;

    $firstOccurredAt = now()->subHour();
    $secondOccurredAt = now();

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/events', validEventPayload([
            'visitor_id' => 'visitor_repeat',
            'occurred_at' => $firstOccurredAt->toIso8601String(),
        ]))
        ->assertStatus(202);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/v1/events', validEventPayload([
            'visitor_id' => 'visitor_repeat',
            'occurred_at' => $secondOccurredAt->toIso8601String(),
        ]))
        ->assertStatus(202);

    expect(Visitor::query()->where('application_id', $application->id)->where('visitor_id', 'visitor_repeat')->count())
        ->toBe(1);

    $visitor = Visitor::query()->where('application_id', $application->id)->where('visitor_id', 'visitor_repeat')->first();

    expect($visitor->last_seen_at->format('Y-m-d H:i:s'))->toBe($secondOccurredAt->format('Y-m-d H:i:s'));
});
