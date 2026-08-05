<?php

use App\Models\Application;
use App\Models\Contact;

test('dashboard exibe o botão de ajuda contextual', function () {
    $admin = superAdmin();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('aria-label="Ajuda"', false);
});

test('tela de aplicações exibe o botão de ajuda contextual', function () {
    $admin = superAdmin();
    Application::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.applications.index'))
        ->assertOk()
        ->assertSee('aria-label="Ajuda"', false);
});

test('tela de contatos exibe o botão de ajuda contextual', function () {
    $admin = superAdmin();
    Contact::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.contacts.index'))
        ->assertOk()
        ->assertSee('aria-label="Ajuda"', false);
});

test('dashboard de analytics exibe o botão de ajuda contextual', function () {
    $admin = superAdmin();
    Application::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.analytics.index'))
        ->assertOk()
        ->assertSee('aria-label="Ajuda"', false);
});
