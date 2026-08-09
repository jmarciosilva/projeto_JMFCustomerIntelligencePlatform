<?php

use App\Livewire\Admin\Affiliate\ProductForm;
use App\Livewire\Admin\Affiliate\ProductIndex;
use App\Models\AffiliateProduct;
use App\Models\AffiliateProgram;
use Livewire\Livewire;

test('super admin visualiza a lista de produtos de afiliados', function () {
    $admin = superAdmin();
    AffiliateProduct::factory()->count(2)->create();

    $this->actingAs($admin)
        ->get(route('admin.affiliate.products.index'))
        ->assertOk()
        ->assertSeeLivewire(ProductIndex::class);
});

test('super admin cria um novo produto de afiliado', function () {
    $admin = superAdmin();
    $program = AffiliateProgram::factory()->create();

    Livewire::actingAs($admin)
        ->test(ProductForm::class, ['product' => null])
        ->set('affiliateProgramId', $program->id)
        ->set('name', 'Cafeteira Espresso XYZ')
        ->set('price', '399.90')
        ->set('commissionPercentage', '8')
        ->set('affiliateUrl', 'https://www.magazinevoce.com.br/produto/cafeteira')
        ->call('save')
        ->assertRedirect(route('admin.affiliate.products.index'));

    $this->assertDatabaseHas('affiliate_products', [
        'affiliate_program_id' => $program->id,
        'application_id' => $program->application_id,
        'name' => 'Cafeteira Espresso XYZ',
        'affiliate_url' => 'https://www.magazinevoce.com.br/produto/cafeteira',
    ]);

    $this->assertDatabaseHas('audit_logs', ['action' => 'affiliate_product.created']);
});

test('affiliate_url é obrigatória e deve ser uma url válida', function () {
    $admin = superAdmin();
    $program = AffiliateProgram::factory()->create();

    Livewire::actingAs($admin)
        ->test(ProductForm::class, ['product' => null])
        ->set('affiliateProgramId', $program->id)
        ->set('name', 'Produto sem link')
        ->set('affiliateUrl', 'não-é-uma-url')
        ->call('save')
        ->assertHasErrors(['affiliateUrl' => 'url']);
});

test('super admin edita um produto de afiliado existente', function () {
    $admin = superAdmin();
    $product = AffiliateProduct::factory()->create(['name' => 'Nome Antigo']);

    Livewire::actingAs($admin)
        ->test(ProductForm::class, ['product' => $product])
        ->set('name', 'Nome Atualizado')
        ->set('availability', AffiliateProduct::AVAILABILITY_OUT_OF_STOCK)
        ->call('save')
        ->assertRedirect(route('admin.affiliate.products.index'));

    $product->refresh();
    expect($product->name)->toBe('Nome Atualizado');
    expect($product->availability)->toBe(AffiliateProduct::AVAILABILITY_OUT_OF_STOCK);
});

test('super admin exclui um produto de afiliado', function () {
    $admin = superAdmin();
    $product = AffiliateProduct::factory()->create();

    Livewire::actingAs($admin)
        ->test(ProductIndex::class)
        ->call('delete', $product)
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('affiliate_products', ['id' => $product->id]);
});

test('lista de produtos filtra por programa de afiliados', function () {
    $admin = superAdmin();
    $programA = AffiliateProgram::factory()->create();
    $programB = AffiliateProgram::factory()->create();

    AffiliateProduct::factory()->for($programA, 'affiliateProgram')->create([
        'application_id' => $programA->application_id,
        'name' => 'Produto A',
    ]);
    AffiliateProduct::factory()->for($programB, 'affiliateProgram')->create([
        'application_id' => $programB->application_id,
        'name' => 'Produto B',
    ]);

    Livewire::actingAs($admin)
        ->test(ProductIndex::class)
        ->set('affiliateProgramId', $programA->id)
        ->assertSee('Produto A')
        ->assertDontSee('Produto B');
});

test('administrador só consegue visualizar produtos de afiliados, não criar', function () {
    $manager = administrador();

    $this->actingAs($manager)
        ->get(route('admin.affiliate.products.index'))
        ->assertOk();

    Livewire::actingAs($manager)
        ->test(ProductForm::class, ['product' => null])
        ->assertForbidden();
});
