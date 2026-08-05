<?php

use App\Models\Application;
use App\Models\Event;
use App\Models\Tenant;
use Illuminate\Support\Str;

function validEventPayload(array $overrides = []): array
{
    return array_merge([
        'event_id' => (string) Str::ulid(),
        'event_name' => 'article.viewed',
        'visitor_id' => 'visitor_001',
        'session_id' => 'session_001',
        'occurred_at' => now()->toIso8601String(),
        'properties' => ['article_id' => 15, 'category' => 'Laravel'],
        'context' => ['page_url' => '/blog/laravel-arquitetura'],
    ], $overrides);
}

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
