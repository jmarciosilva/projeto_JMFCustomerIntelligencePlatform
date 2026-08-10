<?php

use App\Domain\Affiliate\ImportAffiliateConversionsFromCsvAction;
use App\Domain\Affiliate\RegisterAffiliateConversionAction;
use App\Models\AffiliateConversion;
use App\Models\AffiliateProgram;
use App\Models\Application;
use App\Models\Campaign;
use App\Models\Tenant;
use Illuminate\Support\Facades\Storage;

describe('Affiliate Conversions', function () {
    beforeEach(function () {
        $tenant = Tenant::factory()->create();
        $this->application = Application::factory()->create(['tenant_id' => $tenant->id]);
        $this->program = AffiliateProgram::factory()->create(['application_id' => $this->application->id]);
        $this->campaign = Campaign::factory()->create(['application_id' => $this->application->id]);
    });

    it('registra conversão manualmente', function () {
        $action = app(RegisterAffiliateConversionAction::class);
        $product = \App\Models\AffiliateProduct::factory()->create(['affiliate_program_id' => $this->program->id]);

        $conversion = $action->execute([
            'application_id' => $this->application->id,
            'affiliate_program_id' => $this->program->id,
            'affiliate_product_id' => $product->id,
            'order_reference' => 'PED-001',
            'order_date' => '2026-08-10',
            'product_price' => 1000,
            'commission_rate' => 10,
            'commission_value' => 100,
            'status' => AffiliateConversion::STATUS_PENDING,
        ]);

        expect($conversion)->toBeInstanceOf(AffiliateConversion::class);
        expect($conversion->order_reference)->toBe('PED-001');
        expect($conversion->status)->toBe('pending');
        expect($conversion->commission_value)->toEqual(100);
    });

    it('valida duplicação de referência de pedido', function () {
        $action = app(RegisterAffiliateConversionAction::class);
        $product = \App\Models\AffiliateProduct::factory()->create(['affiliate_program_id' => $this->program->id]);

        $action->execute([
            'application_id' => $this->application->id,
            'affiliate_program_id' => $this->program->id,
            'affiliate_product_id' => $product->id,
            'order_reference' => 'PED-001',
            'order_date' => '2026-08-10',
            'product_price' => 1000,
            'commission_rate' => 10,
            'commission_value' => 100,
        ]);

        expect(function () use ($action, $product) {
            $action->execute([
                'application_id' => $this->application->id,
                'affiliate_program_id' => $this->program->id,
                'affiliate_product_id' => $product->id,
                'order_reference' => 'PED-001',
                'order_date' => '2026-08-10',
                'product_price' => 1000,
                'commission_rate' => 10,
                'commission_value' => 100,
            ]);
        })->toThrow(\Exception::class);
    });

    it('importa conversões do CSV', function () {
        $product = \App\Models\AffiliateProduct::factory()->create(['affiliate_program_id' => $this->program->id, 'name' => 'iPhone 15']);

        $csv = "order_reference,product_name,order_date,product_price,commission_rate,commission_value,notes\n";
        $csv .= "PED-001,iPhone 15,2026-08-10,4999.00,15.00,749.85,Venda via link\n";
        $csv .= "PED-002,iPhone 15,2026-08-11,4999.00,15.00,749.85,Venda OK\n";

        $file = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($file, $csv);

        $action = app(ImportAffiliateConversionsFromCsvAction::class);
        $result = $action->execute($file, $this->application, $this->program);

        expect($result['successful'])->toBe(2);
        expect($result['failed'])->toBe(0);

        $conversions = AffiliateConversion::all();
        expect($conversions)->toHaveCount(2);
        expect($conversions[0]->order_reference)->toBe('PED-001');

        unlink($file);
    });

    it('reporta erros no import CSV', function () {
        $csv = "order_reference,product_name,order_date,product_price,commission_rate,commission_value,campaign_name,notes\n";
        $csv .= "PED-001,Produto Inexistente,2026-08-10,4999.00,15.00,749.85,,\n";
        $csv .= "PED-002,iPhone,invalid-date,4999.00,15.00,749.85,,\n";

        $file = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($file, $csv);

        $action = app(ImportAffiliateConversionsFromCsvAction::class);
        $result = $action->execute($file, $this->application, $this->program);

        expect($result['failed'])->toBe(2);
        expect($result['errors'])->toHaveCount(2);

        unlink($file);
    });

    it('importa CSV com linhas válidas e inválidas', function () {
        $product = \App\Models\AffiliateProduct::factory()->create(['affiliate_program_id' => $this->program->id, 'name' => 'iPhone 15']);

        $csv = "order_reference,product_name,order_date,product_price,commission_rate,commission_value,campaign_name,notes\n";
        $csv .= "PED-001,iPhone 15,2026-08-10,4999.00,15.00,749.85,,Venda OK\n";
        $csv .= "PED-002,Produto Inválido,2026-08-10,4999.00,15.00,749.85,,\n";
        $csv .= "PED-003,iPhone 15,2026-08-11,4999.00,15.00,749.85,,Venda OK\n";

        $file = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($file, $csv);

        $action = app(ImportAffiliateConversionsFromCsvAction::class);
        $result = $action->execute($file, $this->application, $this->program);

        expect($result['successful'])->toBe(2);
        expect($result['failed'])->toBe(1);

        unlink($file);
    });

    it('reimportar o mesmo order_reference atualiza a conversão', function () {
        $product = \App\Models\AffiliateProduct::factory()->create(['affiliate_program_id' => $this->program->id, 'name' => 'iPhone 15']);

        $csv = "order_reference,product_name,order_date,product_price,commission_rate,commission_value,notes\n";
        $csv .= "PED-001,iPhone 15,2026-08-10,4999.00,15.00,749.85,Primeira tentativa\n";

        $file = tempnam(sys_get_temp_dir(), 'csv');
        file_put_contents($file, $csv);

        $action = app(ImportAffiliateConversionsFromCsvAction::class);
        $result1 = $action->execute($file, $this->application, $this->program);
        expect($result1['successful'])->toBe(1);

        // Reimportar com dados atualizados
        $csv2 = "order_reference,product_name,order_date,product_price,commission_rate,commission_value,notes\n";
        $csv2 .= "PED-001,iPhone 15,2026-08-10,5000.00,15.00,750.00,Atualizada\n";

        file_put_contents($file, $csv2);
        $result2 = $action->execute($file, $this->application, $this->program);
        expect($result2['successful'])->toBe(1);

        $conversion = AffiliateConversion::where('order_reference', 'PED-001')->first();
        expect($conversion->product_price)->toEqual(5000);
        expect($conversion->notes)->toBe('Atualizada');

        unlink($file);
    });

    it('conversão tem métodos de status', function () {
        $conversion = AffiliateConversion::factory()->create([
            'status' => AffiliateConversion::STATUS_PENDING,
        ]);

        expect($conversion->isPending())->toBeTrue();
        expect($conversion->isApproved())->toBeFalse();

        $conversion->approve();
        expect($conversion->isApproved())->toBeTrue();

        $conversion->markAsPaid();
        expect($conversion->isPaid())->toBeTrue();

        $conversion->cancel();
        expect($conversion->isCancelled())->toBeTrue();
    });

    it('isolado por application_id', function () {
        $tenant2 = Tenant::factory()->create();
        $app2 = Application::factory()->create(['tenant_id' => $tenant2->id]);
        $program2 = AffiliateProgram::factory()->create(['application_id' => $app2->id]);

        $conversion1 = AffiliateConversion::factory()->create([
            'application_id' => $this->application->id,
        ]);

        $conversion2 = AffiliateConversion::factory()->create([
            'application_id' => $app2->id,
        ]);

        $app1Conversions = AffiliateConversion::where('application_id', $this->application->id)->get();
        $app2Conversions = AffiliateConversion::where('application_id', $app2->id)->get();

        expect($app1Conversions)->toHaveCount(1);
        expect($app2Conversions)->toHaveCount(1);
        expect($app1Conversions[0]->id)->toBe($conversion1->id);
        expect($app2Conversions[0]->id)->toBe($conversion2->id);
    });
});
