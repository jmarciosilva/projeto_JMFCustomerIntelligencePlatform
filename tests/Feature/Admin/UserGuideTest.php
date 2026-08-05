<?php

use App\Livewire\Admin\UserGuide;

test('usuário autenticado acessa o guia do usuário', function () {
    $admin = superAdmin();

    $this->actingAs($admin)
        ->get(route('admin.guide'))
        ->assertOk()
        ->assertSeeLivewire(UserGuide::class);
});

test('administrador (sem permissões especiais) também acessa o guia do usuário', function () {
    $manager = administrador();

    $this->actingAs($manager)
        ->get(route('admin.guide'))
        ->assertOk();
});

test('visitante não autenticado é redirecionado para o login', function () {
    $this->get(route('admin.guide'))
        ->assertRedirect(route('admin.login'));
});
