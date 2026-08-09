<?php

use App\Livewire\Admin\Affiliate\ProgramForm;
use App\Livewire\Admin\Affiliate\ProgramIndex;
use App\Models\AffiliateProduct;
use App\Models\AffiliateProgram;
use App\Models\Application;
use App\Models\User;
use Livewire\Livewire;

test('super admin visualiza a lista de programas de afiliados', function () {
    $admin = superAdmin();
    AffiliateProgram::factory()->count(2)->create();

    $this->actingAs($admin)
        ->get(route('admin.affiliate.programs.index'))
        ->assertOk()
        ->assertSeeLivewire(ProgramIndex::class);
});

test('super admin cria um novo programa de afiliados', function () {
    $admin = superAdmin();
    $application = Application::factory()->create();

    Livewire::actingAs($admin)
        ->test(ProgramForm::class, ['program' => null])
        ->set('applicationId', $application->id)
        ->set('name', 'Magazine Você')
        ->set('website', 'https://www.magazinevoce.com.br')
        ->call('save')
        ->assertRedirect(route('admin.affiliate.programs.index'));

    $this->assertDatabaseHas('affiliate_programs', [
        'application_id' => $application->id,
        'name' => 'Magazine Você',
        'slug' => 'magazine-voce',
        'provider' => AffiliateProgram::PROVIDER_MANUAL,
        'status' => AffiliateProgram::STATUS_ACTIVE,
    ]);

    $this->assertDatabaseHas('audit_logs', ['action' => 'affiliate_program.created']);
});

test('super admin edita um programa de afiliados existente', function () {
    $admin = superAdmin();
    $program = AffiliateProgram::factory()->create(['name' => 'Nome Antigo']);

    Livewire::actingAs($admin)
        ->test(ProgramForm::class, ['program' => $program])
        ->set('name', 'Nome Atualizado')
        ->set('status', AffiliateProgram::STATUS_INACTIVE)
        ->call('save')
        ->assertRedirect(route('admin.affiliate.programs.index'));

    $program->refresh();
    expect($program->name)->toBe('Nome Atualizado');
    expect($program->status)->toBe(AffiliateProgram::STATUS_INACTIVE);
});

test('super admin exclui um programa sem produtos vinculados', function () {
    $admin = superAdmin();
    $program = AffiliateProgram::factory()->create();

    Livewire::actingAs($admin)
        ->test(ProgramIndex::class)
        ->call('delete', $program)
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('affiliate_programs', ['id' => $program->id]);
});

test('não é possível excluir um programa com produtos vinculados', function () {
    $admin = superAdmin();
    $program = AffiliateProgram::factory()->create();
    AffiliateProduct::factory()->for($program, 'affiliateProgram')->create([
        'application_id' => $program->application_id,
    ]);

    Livewire::actingAs($admin)
        ->test(ProgramIndex::class)
        ->call('delete', $program)
        ->assertHasErrors('affiliate_program');

    $this->assertDatabaseHas('affiliate_programs', ['id' => $program->id]);
});

test('lista de programas isola por application', function () {
    $admin = superAdmin();
    $applicationA = Application::factory()->create();
    $applicationB = Application::factory()->create();

    $programA = AffiliateProgram::factory()->for($applicationA)->create(['name' => 'Programa A']);
    AffiliateProgram::factory()->for($applicationB)->create(['name' => 'Programa B']);

    Livewire::actingAs($admin)
        ->test(ProgramIndex::class)
        ->set('applicationId', $applicationA->id)
        ->assertSee('Programa A')
        ->assertDontSee('Programa B');

    expect($programA->application_id)->toBe($applicationA->id);
});

test('administrador só consegue visualizar programas de afiliados, não criar', function () {
    $manager = administrador();

    $this->actingAs($manager)
        ->get(route('admin.affiliate.programs.index'))
        ->assertOk();

    Livewire::actingAs($manager)
        ->test(ProgramForm::class, ['program' => null])
        ->assertForbidden();
});

test('usuário sem nenhuma permissão não acessa a lista de programas', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.affiliate.programs.index'))
        ->assertForbidden();
});
