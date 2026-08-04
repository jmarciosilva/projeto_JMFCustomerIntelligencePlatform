<?php

use App\Livewire\Admin\Profile;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

test('a tela de perfil é renderizada com o nome atual do usuário', function () {
    $user = superAdmin(['name' => 'Fulano de Tal']);

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->assertSet('name', 'Fulano de Tal')
        ->assertOk();
});

test('usuário atualiza o próprio nome sem alterar a senha', function () {
    $user = superAdmin(['name' => 'Nome Antigo']);

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->set('name', 'Nome Novo')
        ->call('save')
        ->assertHasNoErrors();

    expect($user->fresh()->name)->toBe('Nome Novo');

    $this->assertDatabaseHas('audit_logs', [
        'action' => 'profile.updated',
        'auditable_id' => $user->id,
    ]);
});

test('usuário altera a própria senha informando a senha atual corretamente', function () {
    $user = superAdmin(['password' => 'senha-atual-123']);

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->set('current_password', 'senha-atual-123')
        ->set('password', 'nova-senha-123')
        ->set('password_confirmation', 'nova-senha-123')
        ->call('save')
        ->assertHasNoErrors();

    expect(Hash::check('nova-senha-123', $user->fresh()->password))->toBeTrue();
});

test('não altera a senha se a senha atual informada estiver incorreta', function () {
    $user = superAdmin(['password' => 'senha-atual-123']);

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->set('current_password', 'senha-errada')
        ->set('password', 'nova-senha-123')
        ->set('password_confirmation', 'nova-senha-123')
        ->call('save')
        ->assertHasErrors('current_password');

    expect(Hash::check('senha-atual-123', $user->fresh()->password))->toBeTrue();
});

test('não altera a senha se a confirmação não corresponder', function () {
    $user = superAdmin(['password' => 'senha-atual-123']);

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->set('current_password', 'senha-atual-123')
        ->set('password', 'nova-senha-123')
        ->set('password_confirmation', 'outra-coisa')
        ->call('save')
        ->assertHasErrors('password');
});
