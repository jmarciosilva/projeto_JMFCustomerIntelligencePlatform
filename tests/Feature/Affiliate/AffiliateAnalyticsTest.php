<?php

use App\Domain\Affiliate\CalculateAffiliateMetricsAction;
use App\Domain\Affiliate\GetTopAffiliateProductsAction;
use App\Models\AffiliateLink;
use App\Models\AffiliateProduct;
use App\Models\AffiliateProgram;
use App\Models\Application;
use App\Models\Campaign;
use App\Models\Tenant;

describe('Affiliate Analytics', function () {
    beforeEach(function () {
        $tenant = Tenant::factory()->create();
        $this->application = Application::factory()->create(['tenant_id' => $tenant->id]);
        $this->program = AffiliateProgram::factory()->create(['application_id' => $this->application->id]);
        $this->campaign = Campaign::factory()->create(['application_id' => $this->application->id]);
    });

    it('CalculateAffiliateMetricsAction retorna array com estrutura esperada', function () {
        $action = app(CalculateAffiliateMetricsAction::class);
        $metrics = $action->execute($this->application);

        expect($metrics)->toBeArray();
        expect($metrics)->toHaveKeys([
            'total_revenue',
            'total_commissions',
            'total_conversions',
            'total_clicks',
            'total_links',
            'ctr',
            'epc',
            'average_commission',
            'average_order_value',
            'period',
        ]);
    });

    it('GetTopAffiliateProductsAction retorna array', function () {
        $product = AffiliateProduct::factory()->create(['affiliate_program_id' => $this->program->id, 'name' => 'Produto A']);

        AffiliateLink::factory()->create([
            'affiliate_product_id' => $product->id,
            'campaign_id' => $this->campaign->id,
            'clicks' => 75,
        ]);

        $action = app(GetTopAffiliateProductsAction::class);
        $products = $action->execute($this->application, limit: 10);

        expect($products)->toBeArray();
        expect($products)->toHaveCount(1);
        expect($products[0])->toHaveKeys(['product_name', 'total_clicks', 'price', 'commission_rate']);
    });

    it('retorna produtos ordenados por clicks descendente', function () {
        $product1 = AffiliateProduct::factory()->create(['affiliate_program_id' => $this->program->id, 'name' => 'Produto A']);
        $product2 = AffiliateProduct::factory()->create(['affiliate_program_id' => $this->program->id, 'name' => 'Produto B']);
        $product3 = AffiliateProduct::factory()->create(['affiliate_program_id' => $this->program->id, 'name' => 'Produto C']);

        AffiliateLink::factory()->create(['affiliate_product_id' => $product1->id, 'campaign_id' => $this->campaign->id, 'clicks' => 100]);
        AffiliateLink::factory()->create(['affiliate_product_id' => $product2->id, 'campaign_id' => $this->campaign->id, 'clicks' => 50]);
        AffiliateLink::factory()->create(['affiliate_product_id' => $product3->id, 'campaign_id' => $this->campaign->id, 'clicks' => 75]);

        $action = app(GetTopAffiliateProductsAction::class);
        $products = $action->execute($this->application, limit: 10);

        expect($products)->toHaveCount(3);
        expect($products[0]['total_clicks'])->toBe(100);
        expect($products[1]['total_clicks'])->toBe(75);
        expect($products[2]['total_clicks'])->toBe(50);
    });

    it('respeita limite de produtos retornados', function () {
        $product1 = AffiliateProduct::factory()->create(['affiliate_program_id' => $this->program->id]);
        $product2 = AffiliateProduct::factory()->create(['affiliate_program_id' => $this->program->id]);
        $product3 = AffiliateProduct::factory()->create(['affiliate_program_id' => $this->program->id]);

        AffiliateLink::factory()->create(['affiliate_product_id' => $product1->id, 'campaign_id' => $this->campaign->id, 'clicks' => 100]);
        AffiliateLink::factory()->create(['affiliate_product_id' => $product2->id, 'campaign_id' => $this->campaign->id, 'clicks' => 50]);
        AffiliateLink::factory()->create(['affiliate_product_id' => $product3->id, 'campaign_id' => $this->campaign->id, 'clicks' => 75]);

        $action = app(GetTopAffiliateProductsAction::class);
        $products = $action->execute($this->application, limit: 2);

        expect($products)->toHaveCount(2);
    });

    it('retorna período com datas válidas', function () {
        $action = app(CalculateAffiliateMetricsAction::class);
        $metrics = $action->execute($this->application, now()->subDays(30), now());

        expect($metrics['period'])->toHaveKeys(['start', 'end']);
        expect($metrics['period']['start'])->toBeString();
        expect($metrics['period']['end'])->toBeString();
    });

    it('GetTopAffiliateProductsAction retorna produtos agrupados', function () {
        $product = AffiliateProduct::factory()->create(['affiliate_program_id' => $this->program->id, 'name' => 'iPhone']);

        AffiliateLink::factory()->create(['affiliate_product_id' => $product->id, 'campaign_id' => $this->campaign->id, 'clicks' => 50]);
        AffiliateLink::factory()->create(['affiliate_product_id' => $product->id, 'campaign_id' => $this->campaign->id, 'clicks' => 30]);

        $action = app(GetTopAffiliateProductsAction::class);
        $products = $action->execute($this->application, limit: 10);

        expect($products)->toHaveCount(1);
        expect($products[0]['product_name'])->toBe('iPhone');
    });
});
