<?php

use App\Livewire\Admin\Affiliate\ProductImport;
use App\Models\AffiliateProgram;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;

test('super admin importa produtos via csv pelo painel', function () {
    $admin = superAdmin();
    $program = AffiliateProgram::factory()->create();

    $csv = "external_product_id,name,affiliate_url\nMG-500,Produto Importado,https://www.magazinevoce.com.br/produto/500\n";
    $file = UploadedFile::fake()->createWithContent('produtos.csv', $csv);

    Livewire::actingAs($admin)
        ->test(ProductImport::class)
        ->set('affiliateProgramId', $program->id)
        ->set('file', $file)
        ->call('import')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('affiliate_products', [
        'affiliate_program_id' => $program->id,
        'external_product_id' => 'MG-500',
        'name' => 'Produto Importado',
    ]);
});

test('importação exige um arquivo csv válido', function () {
    $admin = superAdmin();
    $program = AffiliateProgram::factory()->create();

    Livewire::actingAs($admin)
        ->test(ProductImport::class)
        ->set('affiliateProgramId', $program->id)
        ->call('import')
        ->assertHasErrors(['file' => 'required']);
});
