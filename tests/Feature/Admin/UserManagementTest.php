<?php

use App\Livewire\Admin\Users\UserForm;
use App\Livewire\Admin\Users\UserIndex;
use App\Models\User;
use Livewire\Livewire;

test('super admin visualiza a lista de usuários', function () {
    $admin = superAdmin();
    User::factory()->count(2)->create();

    $this->actingAs($admin)
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertSeeLivewire(UserIndex::class);
});

test('super admin cria um novo usuário com perfil', function () {
    $admin = superAdmin();

    Livewire::actingAs($admin)
        ->test(UserForm::class, ['user' => null])
        ->set('name', 'Novo Admin')
        ->set('email', 'novo-admin@jmf.test')
        ->set('password', 'senha12345')
        ->set('selectedRoles', ['Administrador'])
        ->call('save')
        ->assertRedirect(route('admin.users.index'));

    $this->assertDatabaseHas('users', ['email' => 'novo-admin@jmf.test']);

    $created = User::where('email', 'novo-admin@jmf.test')->first();
    expect($created->hasRole('Administrador'))->toBeTrue();

    $this->assertDatabaseHas('audit_logs', [
        'action' => 'user.created',
    ]);
});

test('super admin edita um usuário existente', function () {
    $admin = superAdmin();
    $target = User::factory()->create(['name' => 'Antigo Nome']);

    Livewire::actingAs($admin)
        ->test(UserForm::class, ['user' => $target])
        ->set('name', 'Nome Atualizado')
        ->set('email', $target->email)
        ->call('save')
        ->assertRedirect(route('admin.users.index'));

    expect($target->fresh()->name)->toBe('Nome Atualizado');

    $this->assertDatabaseHas('audit_logs', [
        'action' => 'user.updated',
        'auditable_id' => $target->id,
    ]);
});

test('super admin exclui um usuário', function () {
    $admin = superAdmin();
    $target = User::factory()->create();

    Livewire::actingAs($admin)
        ->test(UserIndex::class)
        ->call('delete', $target)
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('users', ['id' => $target->id]);
});

test('administrador sem permissão de criação não acessa o formulário de novo usuário', function () {
    $manager = administrador();

    Livewire::actingAs($manager)
        ->test(UserForm::class, ['user' => null])
        ->assertForbidden();
});

test('administrador sem permissão de exclusão não consegue excluir usuário', function () {
    $manager = administrador();
    $target = User::factory()->create();

    Livewire::actingAs($manager)
        ->test(UserIndex::class)
        ->call('delete', $target)
        ->assertForbidden();

    $this->assertDatabaseHas('users', ['id' => $target->id]);
});

test('usuário não pode excluir a própria conta', function () {
    $admin = superAdmin();

    Livewire::actingAs($admin)
        ->test(UserIndex::class)
        ->call('delete', $admin)
        ->assertForbidden();

    $this->assertDatabaseHas('users', ['id' => $admin->id]);
});
