<?php

use App\Livewire\Admin\Analytics\AnalyticsDashboard;
use App\Models\Application;
use App\Models\User;
use Livewire\Livewire;

test('super admin acessa o dashboard de analytics', function () {
    $admin = superAdmin();
    Application::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.analytics.index'))
        ->assertOk()
        ->assertSeeLivewire(AnalyticsDashboard::class);
});

test('usuário sem permissão de analytics.view é bloqueado', function () {
    seedAdminRoles();
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(AnalyticsDashboard::class)
        ->assertForbidden();
});
