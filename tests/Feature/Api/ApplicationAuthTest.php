<?php

use App\Models\Application;
use App\Models\Tenant;

test('token válido retorna os dados corretos de tenant e application', function () {
    $tenant = Tenant::factory()->create(['name' => 'JMF System']);
    $application = Application::factory()->for($tenant)->create(['name' => 'Site Pessoal']);
    $token = $application->createToken('teste')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/ping')
        ->assertOk()
        ->assertJson([
            'tenant' => ['id' => $tenant->id, 'name' => 'JMF System'],
            'application' => ['id' => $application->id, 'name' => 'Site Pessoal'],
        ]);
});

test('requisição sem token é rejeitada', function () {
    $this->getJson('/api/v1/ping')->assertUnauthorized();
});

test('token inválido é rejeitado', function () {
    $this->withHeader('Authorization', 'Bearer token-invalido-qualquer')
        ->getJson('/api/v1/ping')
        ->assertUnauthorized();
});

test('token revogado é rejeitado', function () {
    $application = Application::factory()->create();
    $newToken = $application->createToken('teste');
    $plainText = $newToken->plainTextToken;
    $newToken->accessToken->delete();

    $this->withHeader('Authorization', "Bearer {$plainText}")
        ->getJson('/api/v1/ping')
        ->assertUnauthorized();
});

test('token de aplicação inativa é rejeitado', function () {
    $application = Application::factory()->inactive()->create();
    $token = $application->createToken('teste')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/ping')
        ->assertForbidden();
});

test('token de tenant inativo é rejeitado', function () {
    $tenant = Tenant::factory()->inactive()->create();
    $application = Application::factory()->for($tenant)->create();
    $token = $application->createToken('teste')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/ping')
        ->assertForbidden();
});

test('tokens de applications diferentes nunca retornam dados um do outro', function () {
    $tenantA = Tenant::factory()->create(['name' => 'Tenant A']);
    $appA = Application::factory()->for($tenantA)->create(['name' => 'App A']);
    $tokenA = $appA->createToken('a')->plainTextToken;

    $tenantB = Tenant::factory()->create(['name' => 'Tenant B']);
    $appB = Application::factory()->for($tenantB)->create(['name' => 'App B']);
    $tokenB = $appB->createToken('b')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$tokenA}")
        ->getJson('/api/v1/ping')
        ->assertOk()
        ->assertJsonPath('tenant.name', 'Tenant A')
        ->assertJsonPath('application.name', 'App A');

    // O guard 'sanctum' cacheia o usuário resolvido na própria instância; sem
    // isso, a segunda chamada dentro do mesmo teste reaproveitaria o principal
    // já resolvido na primeira, mascarando um eventual vazamento entre tenants.
    $this->app['auth']->forgetGuards();

    $this->withHeader('Authorization', "Bearer {$tokenB}")
        ->getJson('/api/v1/ping')
        ->assertOk()
        ->assertJsonPath('tenant.name', 'Tenant B')
        ->assertJsonPath('application.name', 'App B');
});
