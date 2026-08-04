<?php

use App\Livewire\Admin\Applications\ApplicationTokens;
use App\Models\Application;
use Livewire\Livewire;

test('super admin acessa a tela de tokens via http', function () {
    $admin = superAdmin();
    $application = Application::factory()->create();
    $application->createToken('token existente');

    $this->actingAs($admin)
        ->get(route('admin.applications.tokens', $application))
        ->assertOk()
        ->assertSeeLivewire(ApplicationTokens::class);
});

test('super admin cria um token e vê o valor em texto puro apenas uma vez', function () {
    $admin = superAdmin();
    $application = Application::factory()->create();

    $component = Livewire::actingAs($admin)
        ->test(ApplicationTokens::class, ['application' => $application])
        ->set('tokenName', 'Produção')
        ->call('create')
        ->assertHasNoErrors();

    expect($component->get('plainTextToken'))->not->toBeNull();

    $this->assertDatabaseHas('personal_access_tokens', [
        'tokenable_type' => Application::class,
        'tokenable_id' => $application->id,
        'name' => 'Produção',
    ]);

    $this->assertDatabaseHas('audit_logs', ['action' => 'application_token.created']);
});

test('super admin revoga um token', function () {
    $admin = superAdmin();
    $application = Application::factory()->create();
    $token = $application->createToken('token de teste');

    Livewire::actingAs($admin)
        ->test(ApplicationTokens::class, ['application' => $application])
        ->call('revoke', $token->accessToken->id)
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('personal_access_tokens', ['id' => $token->accessToken->id]);
    $this->assertDatabaseHas('audit_logs', ['action' => 'application_token.revoked']);
});

test('super admin rotaciona um token, invalidando o anterior', function () {
    $admin = superAdmin();
    $application = Application::factory()->create();
    $token = $application->createToken('token de teste');
    $oldTokenId = $token->accessToken->id;

    $component = Livewire::actingAs($admin)
        ->test(ApplicationTokens::class, ['application' => $application])
        ->call('rotate', $oldTokenId)
        ->assertHasNoErrors();

    expect($component->get('plainTextToken'))->not->toBeNull();

    $this->assertDatabaseMissing('personal_access_tokens', ['id' => $oldTokenId]);
    $this->assertDatabaseHas('personal_access_tokens', [
        'tokenable_id' => $application->id,
        'name' => 'token de teste',
    ]);
    $this->assertDatabaseHas('audit_logs', ['action' => 'application_token.rotated']);
});

test('administrador sem permissão não gerencia tokens', function () {
    $manager = administrador();
    $application = Application::factory()->create();

    Livewire::actingAs($manager)
        ->test(ApplicationTokens::class, ['application' => $application])
        ->assertForbidden();
});
