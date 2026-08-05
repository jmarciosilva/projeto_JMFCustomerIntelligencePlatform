<?php

use App\Livewire\Admin\Applications\ApplicationForm;
use App\Livewire\Admin\Applications\ApplicationIndex;
use App\Models\Application;
use App\Models\Event;
use App\Models\Tenant;
use Livewire\Livewire;

test('super admin visualiza a lista de aplicações', function () {
    $admin = superAdmin();
    Application::factory()->count(2)->create();

    $this->actingAs($admin)
        ->get(route('admin.applications.index'))
        ->assertOk()
        ->assertSeeLivewire(ApplicationIndex::class);
});

test('super admin cria uma nova aplicação vinculada a um tenant', function () {
    $admin = superAdmin();
    $tenant = Tenant::factory()->create();

    Livewire::actingAs($admin)
        ->test(ApplicationForm::class, ['application' => null])
        ->set('name', 'Site Pessoal')
        ->set('tenant_id', $tenant->id)
        ->call('save')
        ->assertRedirect(route('admin.applications.index'));

    $this->assertDatabaseHas('applications', [
        'name' => 'Site Pessoal',
        'tenant_id' => $tenant->id,
    ]);

    $this->assertDatabaseHas('audit_logs', ['action' => 'application.created']);
});

test('super admin edita o nome de uma aplicação existente', function () {
    $admin = superAdmin();
    $application = Application::factory()->create(['name' => 'Nome Antigo']);

    Livewire::actingAs($admin)
        ->test(ApplicationForm::class, ['application' => $application])
        ->set('name', 'Nome Atualizado')
        ->call('save')
        ->assertRedirect(route('admin.applications.index'));

    expect($application->fresh()->name)->toBe('Nome Atualizado');
});

test('super admin exclui uma aplicação e seus tokens junto', function () {
    $admin = superAdmin();
    $application = Application::factory()->create();
    $application->createToken('token de teste');

    Livewire::actingAs($admin)
        ->test(ApplicationIndex::class)
        ->call('delete', $application)
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('applications', ['id' => $application->id]);
    $this->assertDatabaseMissing('personal_access_tokens', [
        'tokenable_type' => Application::class,
        'tokenable_id' => $application->id,
    ]);
});

test('não é possível excluir uma aplicação com eventos vinculados', function () {
    $admin = superAdmin();
    $application = Application::factory()->create();
    Event::factory()->for($application)->create();

    Livewire::actingAs($admin)
        ->test(ApplicationIndex::class)
        ->call('delete', $application)
        ->assertHasErrors('application');

    $this->assertDatabaseHas('applications', ['id' => $application->id]);
});

test('administrador só consegue visualizar aplicações, não criar', function () {
    $manager = administrador();

    $this->actingAs($manager)
        ->get(route('admin.applications.index'))
        ->assertOk();

    Livewire::actingAs($manager)
        ->test(ApplicationForm::class, ['application' => null])
        ->assertForbidden();
});
