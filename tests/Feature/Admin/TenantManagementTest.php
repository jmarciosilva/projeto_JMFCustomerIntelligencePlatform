<?php

use App\Livewire\Admin\Tenants\TenantForm;
use App\Livewire\Admin\Tenants\TenantIndex;
use App\Models\Application;
use App\Models\Tenant;
use Livewire\Livewire;

test('super admin visualiza a lista de tenants', function () {
    $admin = superAdmin();
    Tenant::factory()->count(2)->create();

    $this->actingAs($admin)
        ->get(route('admin.tenants.index'))
        ->assertOk()
        ->assertSeeLivewire(TenantIndex::class);
});

test('super admin cria um novo tenant', function () {
    $admin = superAdmin();

    Livewire::actingAs($admin)
        ->test(TenantForm::class, ['tenant' => null])
        ->set('name', 'JMF System')
        ->call('save')
        ->assertRedirect(route('admin.tenants.index'));

    $this->assertDatabaseHas('tenants', ['name' => 'JMF System', 'slug' => 'jmf-system']);

    $this->assertDatabaseHas('audit_logs', ['action' => 'tenant.created']);
});

test('slug do tenant fica único mesmo com nomes repetidos', function () {
    $admin = superAdmin();
    Tenant::factory()->create(['name' => 'JMF System', 'slug' => 'jmf-system']);

    Livewire::actingAs($admin)
        ->test(TenantForm::class, ['tenant' => null])
        ->set('name', 'JMF System')
        ->call('save')
        ->assertRedirect(route('admin.tenants.index'));

    $this->assertDatabaseHas('tenants', ['name' => 'JMF System', 'slug' => 'jmf-system-2']);
});

test('super admin edita um tenant existente', function () {
    $admin = superAdmin();
    $tenant = Tenant::factory()->create(['name' => 'Nome Antigo']);

    Livewire::actingAs($admin)
        ->test(TenantForm::class, ['tenant' => $tenant])
        ->set('name', 'Nome Atualizado')
        ->call('save')
        ->assertRedirect(route('admin.tenants.index'));

    expect($tenant->fresh()->name)->toBe('Nome Atualizado');
});

test('super admin exclui um tenant sem applications', function () {
    $admin = superAdmin();
    $tenant = Tenant::factory()->create();

    Livewire::actingAs($admin)
        ->test(TenantIndex::class)
        ->call('delete', $tenant)
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('tenants', ['id' => $tenant->id]);
});

test('não é possível excluir um tenant com applications vinculadas', function () {
    $admin = superAdmin();
    $tenant = Tenant::factory()->create();
    Application::factory()->for($tenant)->create();

    Livewire::actingAs($admin)
        ->test(TenantIndex::class)
        ->call('delete', $tenant)
        ->assertHasErrors('tenant');

    $this->assertDatabaseHas('tenants', ['id' => $tenant->id]);
});

test('administrador só consegue visualizar tenants, não criar', function () {
    $manager = administrador();

    $this->actingAs($manager)
        ->get(route('admin.tenants.index'))
        ->assertOk();

    Livewire::actingAs($manager)
        ->test(TenantForm::class, ['tenant' => null])
        ->assertForbidden();
});
