<?php

use App\Domain\Affiliate\JmfRecommendationEngineAction;
use App\Models\AffiliateProduct;
use App\Models\AffiliateProgram;
use App\Models\Application;
use App\Models\ProductOpportunity;
use App\Models\ProductPerformanceScore;
use App\Models\Tenant;
use App\Models\Trend;
use App\Models\Watchlist;

describe('Affiliate Recommendation Engine', function () {
    beforeEach(function () {
        $tenant = Tenant::factory()->create();
        $this->application = Application::factory()->create(['tenant_id' => $tenant->id]);
        $this->program = AffiliateProgram::factory()->create(['application_id' => $this->application->id]);
    });

    it('JmfRecommendationEngineAction retorna array de recomendações', function () {
        $product = AffiliateProduct::factory()->create(['affiliate_program_id' => $this->program->id, 'name' => 'Produto A']);

        $watchlist = Watchlist::factory()->create(['application_id' => $this->application->id]);
        $trend = Trend::factory()->create(['watchlist_id' => $watchlist->id, 'term' => 'tendência legal', 'trend_score' => 85]);

        ProductOpportunity::create([
            'trend_id' => $trend->id,
            'affiliate_product_id' => $product->id,
            'opportunity_score' => 75,
            'opportunity_breakdown' => ['test' => 'data'],
            'commercial_intent' => 'medium',
            'status' => 'pending',
        ]);

        ProductPerformanceScore::create([
            'application_id' => $this->application->id,
            'affiliate_product_id' => $product->id,
            'performance_score' => 80,
        ]);

        $action = app(JmfRecommendationEngineAction::class);
        $recommendations = $action->execute($this->application, limit: 10);

        expect($recommendations)->toBeArray();
        expect($recommendations)->toHaveCount(1);
    });

    it('retorna recomendações com confidence score combinado', function () {
        $product = AffiliateProduct::factory()->create(['affiliate_program_id' => $this->program->id, 'name' => 'Produto B']);

        $watchlist = Watchlist::factory()->create(['application_id' => $this->application->id]);
        $trend = Trend::factory()->create(['watchlist_id' => $watchlist->id, 'trend_score' => 80]);

        ProductOpportunity::create([
            'trend_id' => $trend->id,
            'affiliate_product_id' => $product->id,
            'opportunity_score' => 80,
            'opportunity_breakdown' => ['test' => 'data'],
            'commercial_intent' => 'medium',
            'status' => 'pending',
        ]);

        ProductPerformanceScore::create([
            'application_id' => $this->application->id,
            'affiliate_product_id' => $product->id,
            'performance_score' => 80,
        ]);

        $action = app(JmfRecommendationEngineAction::class);
        $recommendations = $action->execute($this->application);

        expect($recommendations[0])->toHaveKeys(['product_name', 'confidence_score', 'trend_score', 'opportunity_score', 'performance_score', 'reasons']);
        expect($recommendations[0]['confidence_score'])->toBeGreaterThan(0);
    });

    it('ordena recomendações por confidence score descendente', function () {
        $product1 = AffiliateProduct::factory()->create(['affiliate_program_id' => $this->program->id, 'name' => 'Produto 1']);
        $product2 = AffiliateProduct::factory()->create(['affiliate_program_id' => $this->program->id, 'name' => 'Produto 2']);

        $watchlist = Watchlist::factory()->create(['application_id' => $this->application->id]);
        $trend1 = Trend::factory()->create(['watchlist_id' => $watchlist->id, 'trend_score' => 90]);
        $trend2 = Trend::factory()->create(['watchlist_id' => $watchlist->id, 'trend_score' => 50]);

        ProductOpportunity::create([
            'trend_id' => $trend1->id,
            'affiliate_product_id' => $product1->id,
            'opportunity_score' => 90,
            'opportunity_breakdown' => ['test' => 'data'],
            'commercial_intent' => 'high',
            'status' => 'pending',
        ]);

        ProductOpportunity::create([
            'trend_id' => $trend2->id,
            'affiliate_product_id' => $product2->id,
            'opportunity_score' => 50,
            'opportunity_breakdown' => ['test' => 'data'],
            'commercial_intent' => 'low',
            'status' => 'pending',
        ]);

        ProductPerformanceScore::create([
            'application_id' => $this->application->id,
            'affiliate_product_id' => $product1->id,
            'performance_score' => 90,
        ]);

        ProductPerformanceScore::create([
            'application_id' => $this->application->id,
            'affiliate_product_id' => $product2->id,
            'performance_score' => 50,
        ]);

        $action = app(JmfRecommendationEngineAction::class);
        $recommendations = $action->execute($this->application);

        expect($recommendations[0]['confidence_score'])->toBeGreaterThan($recommendations[1]['confidence_score']);
    });

    it('respeita limite de recomendações', function () {
        $watchlist = Watchlist::factory()->create(['application_id' => $this->application->id]);

        for ($i = 0; $i < 5; $i++) {
            $product = AffiliateProduct::factory()->create(['affiliate_program_id' => $this->program->id, 'name' => "Produto $i"]);
            $trend = Trend::factory()->create(['watchlist_id' => $watchlist->id, 'trend_score' => 75]);

            ProductOpportunity::create([
                'trend_id' => $trend->id,
                'affiliate_product_id' => $product->id,
                'opportunity_score' => 75,
                'opportunity_breakdown' => ['test' => 'data'],
                'commercial_intent' => 'medium',
                'status' => 'pending',
            ]);

            ProductPerformanceScore::create([
                'application_id' => $this->application->id,
                'affiliate_product_id' => $product->id,
                'performance_score' => 75,
            ]);
        }

        $action = app(JmfRecommendationEngineAction::class);
        $recommendations = $action->execute($this->application, limit: 3);

        expect($recommendations)->toHaveCount(3);
    });

    it('gera razões para recomendações baseado em scores altos', function () {
        $product = AffiliateProduct::factory()->create(['affiliate_program_id' => $this->program->id]);

        $watchlist = Watchlist::factory()->create(['application_id' => $this->application->id]);
        $trend = Trend::factory()->create(['watchlist_id' => $watchlist->id, 'trend_score' => 85]);

        ProductOpportunity::create([
            'trend_id' => $trend->id,
            'affiliate_product_id' => $product->id,
            'opportunity_score' => 80,
            'opportunity_breakdown' => ['test' => 'data'],
            'commercial_intent' => 'medium',
            'status' => 'pending',
        ]);

        ProductPerformanceScore::create([
            'application_id' => $this->application->id,
            'affiliate_product_id' => $product->id,
            'performance_score' => 80,
        ]);

        $action = app(JmfRecommendationEngineAction::class);
        $recommendations = $action->execute($this->application);

        expect($recommendations[0]['reasons'])->toBeArray();
        expect($recommendations[0]['reasons'])->not->toBeEmpty();
    });
});
