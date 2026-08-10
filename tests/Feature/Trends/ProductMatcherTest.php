<?php

use App\Domain\Trends\ProductMatcher;
use App\Models\AffiliateProduct;
use App\Models\AffiliateProgram;
use App\Models\Application;
use App\Models\Tenant;
use App\Models\Trend;
use App\Models\TrendProductMatch;
use App\Models\Watchlist;

describe('ProductMatcher', function () {
    beforeEach(function () {
        $this->matcher = app(ProductMatcher::class);

        // Setup: tenant, application, programas
        $tenant = Tenant::factory()->create(['slug' => 'test-tenant']);
        $this->application = Application::factory()->create(['tenant_id' => $tenant->id]);

        $this->watchlist = Watchlist::factory()->create([
            'application_id' => $this->application->id,
            'keywords' => ['smartphone', 'iPhone'],
        ]);

        $this->program = AffiliateProgram::factory()->create([
            'application_id' => $this->application->id,
            'name' => 'Magalu Afiliados',
        ]);
    });

    it('encontra produtos por matching de palavra-chave', function () {
        $trend = Trend::factory()->create([
            'application_id' => $this->application->id,
            'watchlist_id' => $this->watchlist->id,
            'term' => 'smartphone',
            'type' => 'keyword',
        ]);

        $product = AffiliateProduct::factory()->create([
            'application_id' => $this->application->id,
            'affiliate_program_id' => $this->program->id,
            'name' => 'Smartphone Samsung Galaxy A53',
            'description' => 'Melhor smartphone para fotografias',
        ]);

        $matches = $this->matcher->match($trend);

        expect($matches)->toHaveCount(1);
        expect($matches->first()['match_score'])->toBeGreaterThan(0);
    });

    it('encontra produtos por categoria exata', function () {
        $trend = Trend::factory()->create([
            'application_id' => $this->application->id,
            'watchlist_id' => $this->watchlist->id,
            'term' => 'Eletrônicos',
            'type' => 'keyword',
        ]);

        $product = AffiliateProduct::factory()->create([
            'application_id' => $this->application->id,
            'affiliate_program_id' => $this->program->id,
            'name' => 'Fone Bluetooth',
            'category' => 'Eletrônicos',
        ]);

        $matches = $this->matcher->match($trend);

        expect($matches)->toHaveCount(1);
        expect($matches->first()['match_breakdown']['category'])->toBe(100.0);
    });

    it('encontra produtos por marca exata', function () {
        $trend = Trend::factory()->create([
            'application_id' => $this->application->id,
            'watchlist_id' => $this->watchlist->id,
            'term' => 'Samsung',
            'type' => 'keyword',
        ]);

        $product = AffiliateProduct::factory()->create([
            'application_id' => $this->application->id,
            'affiliate_program_id' => $this->program->id,
            'name' => 'TV 55 polegadas',
            'brand' => 'Samsung',
        ]);

        $matches = $this->matcher->match($trend);

        expect($matches)->toHaveCount(1);
        expect($matches->first()['match_breakdown']['brand'])->toBe(100.0);
    });

    it('não encontra produtos sem similaridade', function () {
        $trend = Trend::factory()->create([
            'application_id' => $this->application->id,
            'watchlist_id' => $this->watchlist->id,
            'term' => 'extraterrestre',
            'type' => 'keyword',
        ]);

        $product = AffiliateProduct::factory()->create([
            'application_id' => $this->application->id,
            'affiliate_program_id' => $this->program->id,
            'name' => 'Pen drive 32GB',
            'category' => 'Informática',
            'brand' => 'Kingston',
        ]);

        $matches = $this->matcher->match($trend);

        expect($matches)->toHaveCount(0);
    });

    it('ordena matches por score decrescente', function () {
        $trend = Trend::factory()->create([
            'application_id' => $this->application->id,
            'watchlist_id' => $this->watchlist->id,
            'term' => 'Samsung',
            'type' => 'keyword',
        ]);

        // Produto 1: match perfeito (marca Samsung)
        $product1 = AffiliateProduct::factory()->create([
            'application_id' => $this->application->id,
            'affiliate_program_id' => $this->program->id,
            'name' => 'TV Samsung',
            'brand' => 'Samsung',
            'category' => 'Eletrônicos',
        ]);

        // Produto 2: match parcial (nome contém samsung)
        $product2 = AffiliateProduct::factory()->create([
            'application_id' => $this->application->id,
            'affiliate_program_id' => $this->program->id,
            'name' => 'Fone Samsung Sound',
            'brand' => 'LG',
            'category' => 'Áudio',
        ]);

        $matches = $this->matcher->match($trend);

        expect($matches)->toHaveCount(2);
        expect($matches->first()['match_score'])->toBeGreaterThanOrEqual($matches->get(1)['match_score']);
    });

    it('isola matching por application_id', function () {
        $otherTenant = Tenant::factory()->create();
        $otherApp = Application::factory()->create(['tenant_id' => $otherTenant->id]);

        $trend = Trend::factory()->create([
            'application_id' => $this->application->id,
            'watchlist_id' => $this->watchlist->id,
            'term' => 'smartphone',
        ]);

        // Produto em application diferente
        AffiliateProduct::factory()->create([
            'application_id' => $otherApp->id,
            'name' => 'Smartphone Apple iPhone 15',
        ]);

        // Produto na application correta
        AffiliateProduct::factory()->create([
            'application_id' => $this->application->id,
            'affiliate_program_id' => $this->program->id,
            'name' => 'Smartphone Samsung',
        ]);

        $matches = $this->matcher->match($trend);

        // Deve encontrar apenas 1 (da application correta)
        expect($matches)->toHaveCount(1);
    });

    it('persiste matches na tabela', function () {
        $trend = Trend::factory()->create([
            'application_id' => $this->application->id,
            'watchlist_id' => $this->watchlist->id,
            'term' => 'smartphone',
        ]);

        $product = AffiliateProduct::factory()->create([
            'application_id' => $this->application->id,
            'affiliate_program_id' => $this->program->id,
            'name' => 'Smartphone Samsung',
        ]);

        $matches = $this->matcher->match($trend);
        $persisted = $this->matcher->persistMatches($trend, $matches);

        expect($persisted)->toBe(1);
        expect(TrendProductMatch::count())->toBe(1);
        expect(TrendProductMatch::first()->trend_id)->toBe($trend->id);
        expect(TrendProductMatch::first()->affiliate_product_id)->toBe($product->id);
    });

    it('atualiza matches existentes (idempotente)', function () {
        $trend = Trend::factory()->create([
            'application_id' => $this->application->id,
            'watchlist_id' => $this->watchlist->id,
            'term' => 'smartphone',
        ]);

        $product = AffiliateProduct::factory()->create([
            'application_id' => $this->application->id,
            'affiliate_program_id' => $this->program->id,
            'name' => 'Smartphone Samsung',
        ]);

        $matches = $this->matcher->match($trend);
        $this->matcher->persistMatches($trend, $matches);

        $firstMatch = TrendProductMatch::first();
        $scoreFirstTime = $firstMatch->match_score;

        // Executar novamente (simular recálculo)
        $matches = $this->matcher->match($trend);
        $this->matcher->persistMatches($trend, $matches);

        expect(TrendProductMatch::count())->toBe(1);
        expect(TrendProductMatch::first()->match_score)->toBe($scoreFirstTime);
    });

    it('calcula breakdown dos fatores de match', function () {
        $trend = Trend::factory()->create([
            'application_id' => $this->application->id,
            'watchlist_id' => $this->watchlist->id,
            'term' => 'Samsung TV',
        ]);

        $product = AffiliateProduct::factory()->create([
            'application_id' => $this->application->id,
            'affiliate_program_id' => $this->program->id,
            'name' => 'Samsung QLED 55',
            'category' => 'Televisions',
            'brand' => 'Samsung',
        ]);

        $matches = $this->matcher->match($trend);

        expect($matches->first())->toHaveKeys(['trend_id', 'affiliate_product_id', 'match_score', 'match_breakdown']);
        expect($matches->first()['match_breakdown'])->toHaveKeys(['keyword', 'category', 'brand']);
        expect($matches->first()['match_breakdown']['keyword'])->toBeNumeric();
    });
});
