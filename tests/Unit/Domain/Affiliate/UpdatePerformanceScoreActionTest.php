<?php

namespace Tests\Unit\Domain\Affiliate;

use App\Domain\Affiliate\Actions\UpdatePerformanceScoreAction;
use App\Models\AffiliateConversion;
use App\Models\AffiliateProduct;
use App\Models\Application;
use App\Models\ProductOpportunity;
use App\Models\Tenant;
use App\Models\Trend;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdatePerformanceScoreActionTest extends TestCase
{
    protected Tenant $tenant;

    protected Application $application;

    protected Trend $trend;

    protected AffiliateProduct $product;

    protected UpdatePerformanceScoreAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->application = Application::factory()->for($this->tenant)->create();
        $this->trend = Trend::factory()->for($this->application)->create();
        $this->product = AffiliateProduct::factory()->for($this->application)->create();
        $this->action = app(UpdatePerformanceScoreAction::class);
    }

    #[Test]
    public function update_without_conversions_insufficient_data()
    {
        // Sem conversões = INSUFFICIENT_DATA
        $opp = ProductOpportunity::factory()
            ->for($this->application)
            ->for($this->trend)
            ->create();

        $updated = $this->action->execute($opp);

        $this->assertNull($updated->actual_performance_score);
        $this->assertEquals('INSUFFICIENT_DATA', $updated->confidence_level);
    }

    #[Test]
    public function update_with_single_conversion_no_contact_insufficient()
    {
        // 1 conversão sem contact = INSUFFICIENT_DATA (sem fator ConversionRate ou Recurrency)
        $opp = ProductOpportunity::factory()
            ->for($this->application)
            ->for($this->trend)
            ->create();

        AffiliateConversion::factory()->create([
            'product_opportunity_id' => $opp->id,
            'application_id' => $this->application->id,
            'contact_id' => null,
            'status' => 'approved',
        ]);

        $updated = $this->action->execute($opp);

        $this->assertNull($updated->actual_performance_score);
        $this->assertEquals('INSUFFICIENT_DATA', $updated->confidence_level);
    }

    #[Test]
    public function update_with_conversion_and_recurrency_low_confidence()
    {
        // Conversão + Recurrency histórico = LOW confidence (1 fator)
        $contactId = 55555;
        $opp = ProductOpportunity::factory()
            ->for($this->application)
            ->for($this->trend)
            ->create();

        // Criar histórico de conversões para recurrency
        for ($i = 0; $i < 5; $i++) {
            AffiliateConversion::factory()->create([
                'application_id' => $this->application->id,
                'contact_id' => $contactId,
                'status' => 'approved',
                'created_at' => now()->subDays(rand(10, 80)),
            ]);
        }

        // Conversão aprovada
        AffiliateConversion::factory()->create([
            'product_opportunity_id' => $opp->id,
            'application_id' => $this->application->id,
            'contact_id' => $contactId,
            'status' => 'approved',
        ]);

        $updated = $this->action->execute($opp);

        // Com conversão e histórico de contato = LOW confidence (1 fator recurrency)
        $this->assertEquals('LOW', $updated->confidence_level);
        // Recurrency pode ser calculado ou não dependendo se contact_id foi extraído
    }

    #[Test]
    public function update_only_counts_approved_conversions()
    {
        // Pending conversions não contam
        $opp = ProductOpportunity::factory()
            ->for($this->application)
            ->for($this->trend)
            ->create();

        // 5 approved
        for ($i = 0; $i < 5; $i++) {
            AffiliateConversion::factory()->create([
                'product_opportunity_id' => $opp->id,
                'application_id' => $this->application->id,
                'status' => 'approved',
            ]);
        }

        // 10 pending (não contam)
        for ($i = 0; $i < 10; $i++) {
            AffiliateConversion::factory()->create([
                'product_opportunity_id' => $opp->id,
                'application_id' => $this->application->id,
                'status' => 'pending',
            ]);
        }

        $updated = $this->action->execute($opp);

        // Apenas as 5 approved contam
        $this->assertEquals('INSUFFICIENT_DATA', $updated->confidence_level);
    }

    #[Test]
    public function update_persists_breakdown()
    {
        // Breakdown deve ser atualizado
        $contactId = 66666;
        $opp = ProductOpportunity::factory()
            ->for($this->application)
            ->for($this->trend)
            ->create();

        // Histórico
        for ($i = 0; $i < 5; $i++) {
            AffiliateConversion::factory()->create([
                'application_id' => $this->application->id,
                'contact_id' => $contactId,
                'status' => 'approved',
                'created_at' => now()->subDays(rand(10, 80)),
            ]);
        }

        AffiliateConversion::factory()->create([
            'product_opportunity_id' => $opp->id,
            'application_id' => $this->application->id,
            'contact_id' => $contactId,
            'status' => 'approved',
        ]);

        $updated = $this->action->execute($opp);

        $this->assertNotNull($updated->performance_score_breakdown);
        $this->assertIsArray($updated->performance_score_breakdown);
    }

    #[Test]
    public function update_multi_tenant_isolation()
    {
        // Tenant A
        $contactA = 77777;
        $oppA = ProductOpportunity::factory()
            ->for($this->application)
            ->for($this->trend)
            ->create();

        // Tenant B
        $contactB = 88888;
        $tenant2 = Tenant::factory()->create();
        $app2 = Application::factory()->for($tenant2)->create();
        $oppB = ProductOpportunity::factory()
            ->for($app2)
            ->for(Trend::factory()->for($app2)->create())
            ->create();

        // Add conversions to A
        for ($i = 0; $i < 5; $i++) {
            AffiliateConversion::factory()->create([
                'application_id' => $this->application->id,
                'contact_id' => $contactA,
                'status' => 'approved',
                'created_at' => now()->subDays(rand(10, 80)),
            ]);
        }

        AffiliateConversion::factory()->create([
            'product_opportunity_id' => $oppA->id,
            'application_id' => $this->application->id,
            'contact_id' => $contactA,
            'status' => 'approved',
        ]);

        // Add conversions to B
        for ($i = 0; $i < 10; $i++) {
            AffiliateConversion::factory()->create([
                'application_id' => $app2->id,
                'contact_id' => $contactB,
                'status' => 'approved',
                'created_at' => now()->subDays(rand(10, 80)),
            ]);
        }

        AffiliateConversion::factory()->create([
            'product_opportunity_id' => $oppB->id,
            'application_id' => $app2->id,
            'contact_id' => $contactB,
            'status' => 'approved',
        ]);

        $updatedA = $this->action->execute($oppA);
        $updatedB = $this->action->execute($oppB);

        // Ambas devem funcionar independentemente
        $this->assertEquals('LOW', $updatedA->confidence_level);
        $this->assertEquals('LOW', $updatedB->confidence_level);
    }

    #[Test]
    public function update_returns_fresh_instance()
    {
        // Execute retorna fresh() do banco
        $opp = ProductOpportunity::factory()
            ->for($this->application)
            ->for($this->trend)
            ->create();

        $updated = $this->action->execute($opp);

        // Deve ser instância fresh
        $this->assertNotNull($updated->updated_at);
        $this->assertEquals('INSUFFICIENT_DATA', $updated->confidence_level);
    }
}
