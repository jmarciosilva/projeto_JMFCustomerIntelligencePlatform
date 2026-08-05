<?php

use App\Livewire\Admin\Contacts\ContactIndex;
use App\Livewire\Admin\Contacts\ContactShow;
use App\Models\Contact;
use App\Models\User;
use Livewire\Livewire;

test('super admin visualiza a lista de contatos', function () {
    $admin = superAdmin();
    Contact::factory()->count(2)->create();

    $this->actingAs($admin)
        ->get(route('admin.contacts.index'))
        ->assertOk()
        ->assertSeeLivewire(ContactIndex::class);
});

test('super admin visualiza o detalhe de um contato com a timeline', function () {
    $admin = superAdmin();
    $contact = Contact::factory()->create(['name' => 'José Cliente']);

    $this->actingAs($admin)
        ->get(route('admin.contacts.show', $contact))
        ->assertOk()
        ->assertSee('José Cliente');
});

test('administrador (com permissão de view) consegue listar contatos', function () {
    $manager = administrador();

    $this->actingAs($manager)
        ->get(route('admin.contacts.index'))
        ->assertOk();
});

test('usuário sem permissão de contacts.view é bloqueado', function () {
    seedAdminRoles();
    $user = User::factory()->create();
    $contact = Contact::factory()->create();

    Livewire::actingAs($user)
        ->test(ContactIndex::class)
        ->assertForbidden();

    Livewire::actingAs($user)
        ->test(ContactShow::class, ['contact' => $contact])
        ->assertForbidden();
});
