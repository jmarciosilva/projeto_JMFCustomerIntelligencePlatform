<?php

use App\Application\Users\Actions\CreateUserAction;
use App\Application\Users\Actions\DeleteUserAction;
use App\Application\Users\Actions\UpdateUserAction;
use App\Livewire\Admin\Audit\AuditLogIndex;
use App\Livewire\Auth\Login;
use Livewire\Livewire;

test('login bem-sucedido gera entrada de auditoria', function () {
    $user = superAdmin(['email' => 'admin@jmf.test', 'password' => 'senha-valida']);

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'senha-valida')
        ->call('authenticate');

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $user->id,
        'action' => 'login',
    ]);
});

test('login com credenciais inválidas gera entrada de auditoria de falha', function () {
    $user = superAdmin(['email' => 'admin@jmf.test', 'password' => 'senha-valida']);

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'senha-errada')
        ->call('authenticate');

    $this->assertDatabaseHas('audit_logs', [
        'action' => 'login.failed',
    ]);
});

test('ações de gestão de usuários geram entradas de auditoria', function () {
    $admin = superAdmin();
    $this->actingAs($admin);

    $created = app(CreateUserAction::class)->handle('Fulano', 'fulano@jmf.test', 'senha12345');
    app(UpdateUserAction::class)->handle($created, 'Fulano Editado', 'fulano@jmf.test');
    app(DeleteUserAction::class)->handle($created);

    $this->assertDatabaseHas('audit_logs', ['action' => 'user.created', 'auditable_id' => $created->id]);
    $this->assertDatabaseHas('audit_logs', ['action' => 'user.updated', 'auditable_id' => $created->id]);
    $this->assertDatabaseHas('audit_logs', ['action' => 'user.deleted', 'auditable_id' => $created->id]);
});

test('super admin visualiza a tela de auditoria', function () {
    $admin = superAdmin();

    $this->actingAs($admin)
        ->get(route('admin.audit.index'))
        ->assertOk()
        ->assertSeeLivewire(AuditLogIndex::class);
});

test('administrador sem permissão não acessa a tela de auditoria', function () {
    $manager = administrador();

    Livewire::actingAs($manager)
        ->test(AuditLogIndex::class)
        ->assertForbidden();
});
