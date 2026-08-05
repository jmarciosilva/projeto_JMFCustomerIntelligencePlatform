<?php

use App\Models\Application;
use App\Models\ProductAffinity;

test('token válido retorna recomendações no formato esperado', function () {
    $application = Application::factory()->create();
    $token = $application->createToken('teste')->plainTextToken;

    ProductAffinity::factory()->for($application)->create([
        'subject_type' => 'Product', 'subject_id_a' => '1', 'subject_id_b' => '2', 'co_occurrences' => 5,
    ]);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/recommendations?subject_type=Product&subject_id=1')
        ->assertOk()
        ->assertJson([
            'recommendations' => [
                ['subject_id' => '2', 'subject_type' => 'Product', 'score' => 5, 'source' => 'affinity'],
            ],
        ]);
});

test('requisição sem token é rejeitada', function () {
    $this->getJson('/api/v1/recommendations?subject_type=Product&subject_id=1')->assertUnauthorized();
});

test('parâmetros ausentes são rejeitados com 422', function () {
    $application = Application::factory()->create();
    $token = $application->createToken('teste')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/recommendations')
        ->assertStatus(422)
        ->assertJsonValidationErrors(['subject_type', 'subject_id']);
});

test('token de aplicação inativa é rejeitado', function () {
    $application = Application::factory()->inactive()->create();
    $token = $application->createToken('teste')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/recommendations?subject_type=Product&subject_id=1')
        ->assertForbidden();
});

test('recomendações são isoladas por aplicação', function () {
    $applicationA = Application::factory()->create();
    $applicationB = Application::factory()->create();
    $tokenA = $applicationA->createToken('a')->plainTextToken;
    $tokenB = $applicationB->createToken('b')->plainTextToken;

    ProductAffinity::factory()->for($applicationA)->create([
        'subject_type' => 'Product', 'subject_id_a' => '1', 'subject_id_b' => '2', 'co_occurrences' => 5,
    ]);

    $this->withHeader('Authorization', "Bearer {$tokenB}")
        ->getJson('/api/v1/recommendations?subject_type=Product&subject_id=1')
        ->assertOk()
        ->assertJson(['recommendations' => []]);

    // O guard 'sanctum' cacheia o usuário resolvido na própria instância; sem
    // isso, a segunda chamada reaproveitaria a aplicação já resolvida na
    // primeira (ver mesmo comentário em ApplicationAuthTest).
    $this->app['auth']->forgetGuards();

    $this->withHeader('Authorization', "Bearer {$tokenA}")
        ->getJson('/api/v1/recommendations?subject_type=Product&subject_id=1')
        ->assertOk()
        ->assertJsonCount(1, 'recommendations');
});
