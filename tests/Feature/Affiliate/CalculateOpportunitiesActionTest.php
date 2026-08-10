<?php

use App\Application\Affiliate\Actions\CalculateOpportunitiesAction;
use App\Models\AffiliateProduct;
use App\Models\AffiliateProgram;
use App\Models\Application;
use App\Models\ProductOpportunity;
use App\Models\Tenant;
use App\Models\Trend;
use App\Models\TrendProductMatch;
use App\Models\Watchlist;

describe('CalculateOpportunitiesAction', function () {
    beforeEach(function () {
        $this->action = app(CalculateOpportunitiesAction::class);

        $tenant = Tenant::factory()->create();
        $this->application = Application::factory()->create(['tenant_id' => $tenant->id]);

        $this->program = AffiliateProgram::factory()->create([
            'application_id' => $this->application->id,
        ]);
    });

    it('processa todos os matches e cria oportunidades', function () {
        $watchlist = Watchlist::factory()->create(['application_id' => $this->application->id]);

        $trend = Trend::factory()->create([
            'application_id' => $this->application->id,
            'watchlist_id' => $watchlist->id,
            'term' => 'comprar',
            'trend_score' => 80,
        ]);

        $product = AffiliateProduct::factory()->create([
            'application_id' => $this->application->id,
            'affiliate_program_id' => $this->program->id,
        ]);

        TrendProductMatch::factory()->create([
            'trend_id' => $trend->id,
            'affiliate_product_id' => $product->id,
            'match_score' => 75,
        ]);

        $count = $this->action->handle($this->application->id);

        expect($count)->toBe(1);
        expect(ProductOpportunity::count())->toBe(1);
    });

    it('processa múltiplos matches', function () {
        $watchlist = Watchlist::factory()->create(['application_id' => $this->application->id]);

        $trend1 = Trend::factory()->create([
            'application_id' => $this->application->id,
            'watchlist_id' => $watchlist->id,
            'term' => 'smartphone',
            'trend_score' => 80,
        ]);

        $trend2 = Trend::factory()->create([
            'application_id' => $this->application->id,
            'watchlist_id' => $watchlist->id,
            'term' => 'tablet',
            'trend_score' => 70,
        ]);

        $product1 = AffiliateProduct::factory()->create([
            'application_id' => $this->application->id,
            'affiliate_program_id' => $this->program->id,
        ]);

        $product2 = AffiliateProduct::factory()->create([
            'application_id' => $this->application->id,
            'affiliate_program_id' => $this->program->id,
        ]);

        $product3 = AffiliateProduct::factory()->create([
            'application_id' => $this->application->id,
            'affiliate_program_id' => $this->program->id,
        ]);

        // 2 trends × 2 products = 4 matches (sem duplicatas)
        TrendProductMatch::factory()->create([
            'trend_id' => $trend1->id,
            'affiliate_product_id' => $product1->id,
            'match_score' => 80,
        ]);

        TrendProductMatch::factory()->create([
            'trend_id' => $trend1->id,
            'affiliate_product_id' => $product2->id,
            'match_score' => 75,
        ]);

        TrendProductMatch::factory()->create([
            'trend_id' => $trend2->id,
            'affiliate_product_id' => $product1->id,
            'match_score' => 70,
        ]);

        TrendProductMatch::factory()->create([
            'trend_id' => $trend2->id,
            'affiliate_product_id' => $product3->id,
            'match_score' => 65,
        ]);

        $count = $this->action->handle($this->application->id);

        expect($count)->toBe(4);
        expect(ProductOpportunity::count())->toBe(4);
    });

    it('isola por application_id', function () {
        $otherTenant = Tenant::factory()->create();
        $otherApp = Application::factory()->create(['tenant_id' => $otherTenant->id]);
        $otherProgram = AffiliateProgram::factory()->create(['application_id' => $otherApp->id]);

        $watchlist1 = Watchlist::factory()->create(['application_id' => $this->application->id]);
        $watchlist2 = Watchlist::factory()->create(['application_id' => $otherApp->id]);

        $trend1 = Trend::factory()->create([
            'application_id' => $this->application->id,
            'watchlist_id' => $watchlist1->id,
            'trend_score' => 80,
        ]);

        $trend2 = Trend::factory()->create([
            'application_id' => $otherApp->id,
            'watchlist_id' => $watchlist2->id,
            'trend_score' => 80,
        ]);

        $product1 = AffiliateProduct::factory()->create([
            'application_id' => $this->application->id,
            'affiliate_program_id' => $this->program->id,
        ]);

        $product2 = AffiliateProduct::factory()->create([
            'application_id' => $otherApp->id,
            'affiliate_program_id' => $otherProgram->id,
        ]);

        TrendProductMatch::factory()->create([
            'trend_id' => $trend1->id,
            'affiliate_product_id' => $product1->id,
        ]);

        TrendProductMatch::factory()->create([
            'trend_id' => $trend2->id,
            'affiliate_product_id' => $product2->id,
        ]);

        // Processar apenas primeira aplicação
        $count = $this->action->handle($this->application->id);

        expect($count)->toBe(1);
        $opp = ProductOpportunity::first();
        expect($opp->trend->application_id)->toBe($this->application->id);
    });

    it('é idempotente (updateOrCreate)', function () {
        $watchlist = Watchlist::factory()->create(['application_id' => $this->application->id]);

        $trend = Trend::factory()->create([
            'application_id' => $this->application->id,
            'watchlist_id' => $watchlist->id,
            'term' => 'comprar',
            'trend_score' => 80,
        ]);

        $product = AffiliateProduct::factory()->create([
            'application_id' => $this->application->id,
            'affiliate_program_id' => $this->program->id,
        ]);

        $match = TrendProductMatch::factory()->create([
            'trend_id' => $trend->id,
            'affiliate_product_id' => $product->id,
            'match_score' => 75,
        ]);

        $this->action->handleOne($match);
        $firstScore = ProductOpportunity::first()->opportunity_score;

        // Executar novamente
        $this->action->handleOne($match);

        expect(ProductOpportunity::count())->toBe(1);
        expect(ProductOpportunity::first()->opportunity_score)->toBe($firstScore);
    });

    it('atualiza scores quando trend muda', function () {
        $watchlist = Watchlist::factory()->create(['application_id' => $this->application->id]);

        $trend = Trend::factory()->create([
            'application_id' => $this->application->id,
            'watchlist_id' => $watchlist->id,
            'term' => 'comprar',
            'trend_score' => 50,
        ]);

        $product = AffiliateProduct::factory()->create([
            'application_id' => $this->application->id,
            'affiliate_program_id' => $this->program->id,
        ]);

        $match = TrendProductMatch::factory()->create([
            'trend_id' => $trend->id,
            'affiliate_product_id' => $product->id,
            'match_score' => 75,
        ]);

        $this->action->handleOne($match);
        $scoreFirst = ProductOpportunity::first()->opportunity_score;

        // Aumentar trend score
        $trend->update(['trend_score' => 85]); // Mudança mais significativa

        // Recarregar match para atualizar o relacionamento
        $match = TrendProductMatch::find($match->id);
        $match->trend->refresh(); // Refresh do trend para obter novo score

        $this->action->handleOne($match);
        $scoreSecond = ProductOpportunity::first()->opportunity_score;

        expect($scoreSecond)->toBeGreaterThan($scoreFirst);
    });

    it('cria oportunidades com status pending por padrão', function () {
        $watchlist = Watchlist::factory()->create(['application_id' => $this->application->id]);

        $trend = Trend::factory()->create([
            'application_id' => $this->application->id,
            'watchlist_id' => $watchlist->id,
            'trend_score' => 80,
        ]);

        $product = AffiliateProduct::factory()->create([
            'application_id' => $this->application->id,
            'affiliate_program_id' => $this->program->id,
        ]);

        TrendProductMatch::factory()->create([
            'trend_id' => $trend->id,
            'affiliate_product_id' => $product->id,
        ]);

        $this->action->handle($this->application->id);

        $opp = ProductOpportunity::first();
        expect($opp->status)->toBe(ProductOpportunity::STATUS_PENDING);
    });
});
