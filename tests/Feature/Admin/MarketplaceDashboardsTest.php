<?php

use App\Livewire\Marketplace\ContactsList;
use App\Livewire\Marketplace\Dashboard;
use App\Models\Application;
use App\Models\Contact;
use App\Models\Event;
use App\Models\Tenant;
use Livewire\Livewire;

test('super admin visualiza o dashboard de marketplace sem erro', function () {
    $admin = superAdmin();
    Application::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.marketplace.dashboard'))
        ->assertOk()
        ->assertSeeLivewire(Dashboard::class);
});

test('dashboard de marketplace calcula métricas da aplicação selecionada, não de uma aplicação vazia', function () {
    $admin = superAdmin();
    $application = Application::factory()->create(['is_active' => true]);

    Event::factory()->create([
        'application_id' => $application->id,
        'event_name' => 'product.viewed',
        'properties' => ['product_id' => 42, 'seller_id' => 1],
    ]);

    $this->actingAs($admin);

    Livewire::test(Dashboard::class)
        ->set('applicationId', $application->id)
        ->assertSet('metrics.product_views', 1);
});

test('dashboard de marketplace sem nenhuma aplicação cadastrada não quebra', function () {
    $admin = superAdmin();

    $this->actingAs($admin)
        ->get(route('admin.marketplace.dashboard'))
        ->assertOk()
        ->assertSee('Nenhuma aplicação cadastrada');
});

test('super admin visualiza a lista de contatos do marketplace sem erro', function () {
    $admin = superAdmin();
    Tenant::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.marketplace.contacts'))
        ->assertOk()
        ->assertSeeLivewire(ContactsList::class);
});

test('lista de contatos do marketplace mostra contatos do tenant selecionado, não de um tenant vazio', function () {
    $admin = superAdmin();
    $tenant = Tenant::factory()->create(['is_active' => true]);
    Contact::factory()->create(['tenant_id' => $tenant->id, 'name' => 'Cliente Teste Marketplace']);

    $this->actingAs($admin);

    Livewire::test(ContactsList::class)
        ->set('tenantId', $tenant->id)
        ->assertSee('Cliente Teste Marketplace');
});

test('lista de contatos isola por tenant selecionado', function () {
    $admin = superAdmin();
    $tenant1 = Tenant::factory()->create();
    $tenant2 = Tenant::factory()->create();
    Contact::factory()->create(['tenant_id' => $tenant1->id, 'name' => 'Contato Tenant 1']);
    Contact::factory()->create(['tenant_id' => $tenant2->id, 'name' => 'Contato Tenant 2']);

    $this->actingAs($admin);

    Livewire::test(ContactsList::class)
        ->set('tenantId', $tenant1->id)
        ->assertSee('Contato Tenant 1')
        ->assertDontSee('Contato Tenant 2');
});
