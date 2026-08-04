<?php

use App\Livewire\Auth\Login;
use Livewire\Livewire;

test('a página de login é renderizada', function () {
    $this->get(route('admin.login'))
        ->assertOk()
        ->assertSeeLivewire(Login::class);
});

test('credenciais válidas autenticam e redirecionam para o dashboard', function () {
    $user = superAdmin(['email' => 'admin@jmf.test', 'password' => 'senha-valida']);

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'senha-valida')
        ->call('authenticate')
        ->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs($user);
});

test('credenciais inválidas mostram erro e não autenticam', function () {
    $user = superAdmin(['email' => 'admin@jmf.test', 'password' => 'senha-valida']);

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'senha-errada')
        ->call('authenticate')
        ->assertHasErrors('email');

    $this->assertGuest();
});

test('usuário desativado não consegue autenticar', function () {
    $user = superAdmin(['email' => 'admin@jmf.test', 'password' => 'senha-valida', 'is_active' => false]);

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'senha-valida')
        ->call('authenticate')
        ->assertHasErrors('email');

    $this->assertGuest();
});

test('visitante é redirecionado para o login ao acessar área administrativa', function () {
    $this->get(route('admin.dashboard'))
        ->assertRedirect(route('admin.login'));
});

test('usuário autenticado é redirecionado para fora do login', function () {
    $user = superAdmin();

    $this->actingAs($user)
        ->get(route('admin.login'))
        ->assertRedirect(route('admin.dashboard'));
});
