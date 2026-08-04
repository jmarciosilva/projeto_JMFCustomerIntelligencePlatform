<?php

test('usuário autenticado consegue fazer logout', function () {
    $user = superAdmin();

    $this->actingAs($user)
        ->post(route('admin.logout'))
        ->assertRedirect(route('admin.login'));

    $this->assertGuest();
});

test('logout registra entrada de auditoria', function () {
    $user = superAdmin();

    $this->actingAs($user)->post(route('admin.logout'));

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $user->id,
        'action' => 'logout',
    ]);
});
