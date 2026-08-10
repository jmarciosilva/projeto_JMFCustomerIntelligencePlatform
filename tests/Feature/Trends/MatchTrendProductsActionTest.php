<?php

use App\Application\Trends\Actions\MatchTrendProductsAction;
use App\Models\AffiliateProduct;
use App\Models\AffiliateProgram;
use App\Models\Application;
use App\Models\Tenant;
use App\Models\Trend;
use App\Models\TrendProductMatch;
use App\Models\Watchlist;

describe('MatchTrendProductsAction', function () {
    beforeEach(function () {
        $this->action = app(MatchTrendProductsAction::class);

        $tenant = Tenant::factory()->create();
        $this->application = Application::factory()->create(['tenant_id' => $tenant->id]);

        $this->program = AffiliateProgram::factory()->create([
            'application_id' => $this->application->id,
        ]);
    });

    it('processa um trend específico', function () {
        $watchlist = Watchlist::factory()->create(['application_id' => $this->application->id]);

        $trend = Trend::factory()->create([
            'application_id' => $this->application->id,
            'watchlist_id' => $watchlist->id,
            'term' => 'smartphone',
            'status' => Trend::STATUS_ACTIVE,
        ]);

        AffiliateProduct::factory()->create([
            'application_id' => $this->application->id,
            'affiliate_program_id' => $this->program->id,
            'name' => 'Smartphone Samsung Galaxy',
        ]);

        $matched = $this->action->handleOne($trend);

        expect($matched)->toBeGreaterThan(0);
        expect(TrendProductMatch::count())->toBe($matched);
    });

    it('processa todos os trends ativos de uma application', function () {
        $watchlist = Watchlist::factory()->create(['application_id' => $this->application->id]);

        // 3 trends ativos com termos únicos
        Trend::factory()->create([
            'application_id' => $this->application->id,
            'watchlist_id' => $watchlist->id,
            'status' => Trend::STATUS_ACTIVE,
            'term' => 'smartphone',
        ]);
        Trend::factory()->create([
            'application_id' => $this->application->id,
            'watchlist_id' => $watchlist->id,
            'status' => Trend::STATUS_ACTIVE,
            'term' => 'tablet',
        ]);
        Trend::factory()->create([
            'application_id' => $this->application->id,
            'watchlist_id' => $watchlist->id,
            'status' => Trend::STATUS_ACTIVE,
            'term' => 'fone',
        ]);

        // 1 trend inativo (deve ser ignorado)
        Trend::factory()->create([
            'application_id' => $this->application->id,
            'watchlist_id' => $watchlist->id,
            'status' => Trend::STATUS_INACTIVE,
            'term' => 'notebook',
        ]);

        // 5 produtos
        AffiliateProduct::factory(5)->create([
            'application_id' => $this->application->id,
            'affiliate_program_id' => $this->program->id,
            'name' => 'Smartphone Samsung',
        ]);

        $totalMatched = $this->action->handle($this->application->id);

        expect($totalMatched)->toBeGreaterThan(0);
        // 3 trends × (até 5 produtos cada) = até 15 matches possíveis
        expect(TrendProductMatch::count())->toBeLessThanOrEqual(15);
    });

    it('isola matching por application_id', function () {
        $otherTenant = Tenant::factory()->create();
        $otherApp = Application::factory()->create(['tenant_id' => $otherTenant->id]);

        $watchlist1 = Watchlist::factory()->create(['application_id' => $this->application->id]);
        $watchlist2 = Watchlist::factory()->create(['application_id' => $otherApp->id]);

        // Trends e produtos de aplicação 1
        Trend::factory()->create([
            'application_id' => $this->application->id,
            'watchlist_id' => $watchlist1->id,
            'term' => 'smartphone',
            'status' => Trend::STATUS_ACTIVE,
        ]);

        AffiliateProduct::factory()->create([
            'application_id' => $this->application->id,
            'affiliate_program_id' => $this->program->id,
            'name' => 'Smartphone Samsung',
        ]);

        // Trends e produtos de aplicação 2
        $otherProgram = AffiliateProgram::factory()->create(['application_id' => $otherApp->id]);
        Trend::factory()->create([
            'application_id' => $otherApp->id,
            'watchlist_id' => $watchlist2->id,
            'term' => 'smartphone',
            'status' => Trend::STATUS_ACTIVE,
        ]);

        AffiliateProduct::factory()->create([
            'application_id' => $otherApp->id,
            'affiliate_program_id' => $otherProgram->id,
            'name' => 'Smartphone Samsung',
        ]);

        // Processar apenas aplicação 1
        $matched = $this->action->handle($this->application->id);

        // Verificar que os trends relacionados pertencem à aplicação correta
        $trends = Trend::whereHas('productMatches')->pluck('application_id')->unique();
        expect($trends)->toHaveCount(1);
        expect($trends->first())->toBe($this->application->id);
    });

    it('ignora trends inativos', function () {
        $watchlist = Watchlist::factory()->create(['application_id' => $this->application->id]);

        Trend::factory()->create([
            'application_id' => $this->application->id,
            'watchlist_id' => $watchlist->id,
            'status' => Trend::STATUS_INACTIVE,
        ]);

        AffiliateProduct::factory()->create([
            'application_id' => $this->application->id,
            'affiliate_program_id' => $this->program->id,
            'name' => 'Smartphone Samsung',
        ]);

        $matched = $this->action->handle($this->application->id);

        expect($matched)->toBe(0);
        expect(TrendProductMatch::count())->toBe(0);
    });

    it('retorna quantidade correta de matches', function () {
        $watchlist = Watchlist::factory()->create(['application_id' => $this->application->id]);

        $trend = Trend::factory()->create([
            'application_id' => $this->application->id,
            'watchlist_id' => $watchlist->id,
            'term' => 'fone',
            'status' => Trend::STATUS_ACTIVE,
        ]);

        // 3 produtos que combinam
        AffiliateProduct::factory(3)->create([
            'application_id' => $this->application->id,
            'affiliate_program_id' => $this->program->id,
            'name' => 'Fone Bluetooth',
        ]);

        $matched = $this->action->handleOne($trend);

        expect($matched)->toBe(3);
    });
});
