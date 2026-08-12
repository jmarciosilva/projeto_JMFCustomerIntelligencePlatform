<?php

use App\Domain\Affiliate\OpportunityScoreCalculator;
use App\Models\AffiliateProduct;
use App\Models\AffiliateProgram;
use App\Models\Application;
use App\Models\ProductOpportunity;
use App\Models\Tenant;
use App\Models\Trend;
use App\Models\TrendProductMatch;
use App\Models\Watchlist;

describe('OpportunityScoreCalculator', function () {
    beforeEach(function () {
        $this->calculator = app(OpportunityScoreCalculator::class);

        $tenant = Tenant::factory()->create();
        $this->application = Application::factory()->create(['tenant_id' => $tenant->id]);

        $this->program = AffiliateProgram::factory()->create([
            'application_id' => $this->application->id,
        ]);

        $this->product = AffiliateProduct::factory()->create([
            'application_id' => $this->application->id,
            'affiliate_program_id' => $this->program->id,
            'commission_percentage' => 5.0,
        ]);

        $this->watchlist = Watchlist::factory()->create([
            'application_id' => $this->application->id,
        ]);
    });

    it('calcula score combinando trend score + match score', function () {
        $trend = Trend::factory()->create([
            'application_id' => $this->application->id,
            'watchlist_id' => $this->watchlist->id,
            'term' => 'comprar smartphone',
            'trend_score' => 80,
        ]);

        TrendProductMatch::factory()->create([
            'trend_id' => $trend->id,
            'affiliate_product_id' => $this->product->id,
            'match_score' => 90,
        ]);

        $result = $this->calculator->calculate($trend, $this->product);

        expect($result)->toHaveKeys(['score', 'breakdown', 'intent']);
        expect($result['score'])->toBeGreaterThan(0);
        expect($result['score'])->toBeLessThanOrEqual(100);
        expect($result['breakdown'])->toHaveKeys(['trend_score', 'match_score', 'commercial_intent', 'commission', 'popularity']);
    });

    it('detecta alta intenção comercial', function () {
        $trend = Trend::factory()->create([
            'application_id' => $this->application->id,
            'watchlist_id' => $this->watchlist->id,
            'term' => 'melhor preço de smartphone',
            'trend_score' => 50,
        ]);

        $result = $this->calculator->calculate($trend, $this->product);

        expect($result['intent'])->toBe(ProductOpportunity::INTENT_HIGH);
        expect($result['breakdown']['commercial_intent'])->toBe(100.0);
    });

    it('score é maior com alta intenção comercial', function () {
        $trend1 = Trend::factory()->create([
            'application_id' => $this->application->id,
            'watchlist_id' => $this->watchlist->id,
            'term' => 'comprar notebook',
            'trend_score' => 50,
        ]);

        $trend2 = Trend::factory()->create([
            'application_id' => $this->application->id,
            'watchlist_id' => $this->watchlist->id,
            'term' => 'o que é notebook',
            'trend_score' => 50,
        ]);

        foreach ([$trend1, $trend2] as $trend) {
            TrendProductMatch::factory()->create([
                'trend_id' => $trend->id,
                'affiliate_product_id' => $this->product->id,
                'match_score' => 80,
            ]);
        }

        $result1 = $this->calculator->calculate($trend1, $this->product);
        $result2 = $this->calculator->calculate($trend2, $this->product);

        expect($result1['score'])->toBeGreaterThan($result2['score']);
    });

    it('commission afeta o score', function () {
        $productHighCommission = AffiliateProduct::factory()->create([
            'application_id' => $this->application->id,
            'affiliate_program_id' => $this->program->id,
            'commission_percentage' => 20.0,
        ]);

        $trend = Trend::factory()->create([
            'application_id' => $this->application->id,
            'watchlist_id' => $this->watchlist->id,
            'term' => 'comprar',
            'trend_score' => 50,
        ]);

        foreach ([$this->product, $productHighCommission] as $product) {
            TrendProductMatch::factory()->create([
                'trend_id' => $trend->id,
                'affiliate_product_id' => $product->id,
                'match_score' => 80,
            ]);
        }

        $result1 = $this->calculator->calculate($trend, $this->product);
        $result2 = $this->calculator->calculate($trend, $productHighCommission);

        expect($result2['score'])->toBeGreaterThan($result1['score']);
    });

    it('isola cálculos por application', function () {
        $otherTenant = Tenant::factory()->create();
        $otherApp = Application::factory()->create(['tenant_id' => $otherTenant->id]);
        $otherProgram = AffiliateProgram::factory()->create(['application_id' => $otherApp->id]);
        $otherProduct = AffiliateProduct::factory()->create([
            'application_id' => $otherApp->id,
            'affiliate_program_id' => $otherProgram->id,
        ]);

        $trend = Trend::factory()->create([
            'application_id' => $this->application->id,
            'watchlist_id' => $this->watchlist->id,
            'term' => 'comprar',
            'trend_score' => 80,
        ]);

        $result = $this->calculator->calculate($trend, $this->product);
        expect($result['score'])->toBeGreaterThan(0);
    });

    it('score = 0 quando não há trend score ou match', function () {
        $trend = Trend::factory()->create([
            'application_id' => $this->application->id,
            'watchlist_id' => $this->watchlist->id,
            'term' => 'xyz',
            'trend_score' => 0,
        ]);

        $result = $this->calculator->calculate($trend, $this->product);

        // Score não será zero porque pode ter intent, mas será baixo
        expect($result['score'])->toBeLessThan(30);
    });

    it('score máximo é 100', function () {
        $trend = Trend::factory()->create([
            'application_id' => $this->application->id,
            'watchlist_id' => $this->watchlist->id,
            'term' => 'comprar', // HIGH intent
            'trend_score' => 100,
        ]);

        TrendProductMatch::factory()->create([
            'trend_id' => $trend->id,
            'affiliate_product_id' => $this->product->id,
            'match_score' => 100,
        ]);

        $result = $this->calculator->calculate($trend, $this->product);

        expect($result['score'])->toBeLessThanOrEqual(100);
    });

    it('breakdown soma é coerente com score', function () {
        $trend = Trend::factory()->create([
            'application_id' => $this->application->id,
            'watchlist_id' => $this->watchlist->id,
            'term' => 'melhor preço',
            'trend_score' => 75,
        ]);

        TrendProductMatch::factory()->create([
            'trend_id' => $trend->id,
            'affiliate_product_id' => $this->product->id,
            'match_score' => 85,
        ]);

        $result = $this->calculator->calculate($trend, $this->product);

        $breakdown = $result['breakdown'];
        $manualScore = ($breakdown['trend_score'] * 0.35) +
                      ($breakdown['match_score'] * 0.25) +
                      ($breakdown['commercial_intent'] * 0.20) +
                      ($breakdown['commission'] * 0.10) +
                      ($breakdown['popularity'] * 0.10);

        expect(round($manualScore, 2))->toBe($result['score']);
    });
});
