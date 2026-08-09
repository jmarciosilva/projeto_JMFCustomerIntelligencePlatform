<?php

use App\Application\Affiliate\Actions\ImportAffiliateProductsFromCsvAction;
use App\Models\AffiliateProduct;
use App\Models\AffiliateProgram;
use App\Models\IntegrationLog;

function writeAffiliateCsv(string $contents): string
{
    $path = tempnam(sys_get_temp_dir(), 'affiliate_csv_');
    file_put_contents($path, $contents);

    return $path;
}

test('importa produtos válidos e ignora linhas inválidas', function () {
    $program = AffiliateProgram::factory()->create();

    $csv = <<<'CSV'
    external_product_id,name,description,category,brand,price,original_price,commission_percentage,estimated_commission,affiliate_url,image_url,availability
    MG-001,Cafeteira Espresso,Uma boa cafeteira,Casa,MarcaX,399.90,499.90,8,32,https://www.magazinevoce.com.br/produto/cafeteira,https://img.example.com/cafeteira.jpg,in_stock
    MG-002,,Sem nome,Casa,MarcaX,199.90,,5,,https://www.magazinevoce.com.br/produto/sem-nome,,unknown
    MG-003,Produto sem link,Sem affiliate_url,Casa,MarcaX,99.90,,5,,,,unknown
    MG-004,Produto com preço inválido,Preço não numérico,Casa,MarcaX,abc,,5,,https://www.magazinevoce.com.br/produto/preco-invalido,,unknown
    CSV;

    $path = writeAffiliateCsv($csv);

    $action = app(ImportAffiliateProductsFromCsvAction::class);
    $summary = $action->handle($program, $path);

    expect($summary['processed'])->toBe(1);
    expect($summary['failed'])->toBe(3);
    expect($summary['errors'])->toHaveCount(3);

    $this->assertDatabaseHas('affiliate_products', [
        'affiliate_program_id' => $program->id,
        'external_product_id' => 'MG-001',
        'name' => 'Cafeteira Espresso',
    ]);
    $this->assertDatabaseMissing('affiliate_products', ['external_product_id' => 'MG-002']);
    $this->assertDatabaseMissing('affiliate_products', ['external_product_id' => 'MG-003']);
    $this->assertDatabaseMissing('affiliate_products', ['external_product_id' => 'MG-004']);

    $this->assertDatabaseHas('integration_logs', [
        'application_id' => $program->application_id,
        'integration' => 'affiliate.csv_import',
        'status' => IntegrationLog::STATUS_PARTIAL,
        'items_processed' => 1,
        'items_failed' => 3,
    ]);

    @unlink($path);
});

test('reimportar o mesmo external_product_id atualiza o produto em vez de duplicar', function () {
    $program = AffiliateProgram::factory()->create();

    $csvV1 = "external_product_id,name,affiliate_url\nMG-100,Nome Antigo,https://www.magazinevoce.com.br/produto/1\n";
    $csvV2 = "external_product_id,name,affiliate_url\nMG-100,Nome Novo,https://www.magazinevoce.com.br/produto/1\n";

    $action = app(ImportAffiliateProductsFromCsvAction::class);
    $action->handle($program, writeAffiliateCsv($csvV1));
    $action->handle($program, writeAffiliateCsv($csvV2));

    expect(AffiliateProduct::where('affiliate_program_id', $program->id)->count())->toBe(1);
    $this->assertDatabaseHas('affiliate_products', [
        'affiliate_program_id' => $program->id,
        'external_product_id' => 'MG-100',
        'name' => 'Nome Novo',
    ]);
});

test('import totalmente bem-sucedido registra IntegrationLog como success', function () {
    $program = AffiliateProgram::factory()->create();

    $csv = "external_product_id,name,affiliate_url\nMG-200,Produto OK,https://www.magazinevoce.com.br/produto/2\n";

    $action = app(ImportAffiliateProductsFromCsvAction::class);
    $summary = $action->handle($program, writeAffiliateCsv($csv));

    expect($summary['processed'])->toBe(1);
    expect($summary['failed'])->toBe(0);

    $this->assertDatabaseHas('integration_logs', [
        'integration' => 'affiliate.csv_import',
        'status' => IntegrationLog::STATUS_SUCCESS,
    ]);
});
