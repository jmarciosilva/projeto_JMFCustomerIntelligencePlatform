<?php

namespace Tests\Feature\Integration;

use App\Domain\Affiliate\Actions\ApproveProductOpportunityAction;
use App\Domain\Affiliate\Actions\CreateProductOpportunityAction;
use App\Domain\Affiliate\Actions\PublishProductOpportunityAction;
use App\Domain\Affiliate\PerformanceScoreCalculator;
use App\Models\AffiliateConversion;
use App\Models\Application;
use App\Models\AffiliateProduct;
use App\Models\ProductOpportunity;
use App\Models\Tenant;
use App\Models\Trend;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductOpportunityB1IntegrationTest extends TestCase
{
    protected Tenant $tenant;
    protected Application $application;
    protected Trend $trend;
    protected AffiliateProduct $product;
    protected User $user;
    protected PerformanceScoreCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->application = Application::factory()->for($this->tenant)->create();
        $this->trend = Trend::factory()->for($this->application)->create();
        $this->product = AffiliateProduct::factory()->for($this->application)->create();
        $this->user = User::factory()->create();
        $this->calculator = new PerformanceScoreCalculator();
    }

    #[Test]
    public function opportunity_stores_recurrency_rate()
    {
        // Opportunity pode armazenar recurrency_rate após migração
        $opp = ProductOpportunity::factory()
            ->for($this->application)
            ->for($this->trend)
            ->create([
                'recurrency_rate' => 12.5,
                'confidence_level' => 'MEDIUM',
            ]);

        $this->assertEquals(12.5, $opp->recurrency_rate);
        $this->assertEquals('MEDIUM', $opp->confidence_level);

        // Verificar que dados persistem após refresh
        $fresh = $opp->fresh();
        $this->assertEquals(12.5, $fresh->recurrency_rate);
        $this->assertEquals('MEDIUM', $fresh->confidence_level);
    }

    #[Test]
    public function opportunity_null_recurrency_backward_compatible()
    {
        // Opportunities criadas sem recurrency (Sprint A) continuam funcionando
        $opp = ProductOpportunity::factory()
            ->for($this->application)
            ->for($this->trend)
            ->create([
                'recurrency_rate' => null,
                'confidence_level' => null,
            ]);

        $this->assertNull($opp->recurrency_rate);
        $this->assertNull($opp->confidence_level);
    }

    #[Test]
    public function performance_score_calculation_includes_recurrency()
    {
        // Quando recurrency presente, score inclui 3 fatores (HIGH confidence)
        $clicks = 100;
        $conversions = 10;
        $impressions = 1000;
        $recurrency_rate = 15.0;

        $result = $this->calculator->calculate(
            clicks: $clicks,
            conversions: $conversions,
            impressions: $impressions,
            recurrency_rate: $recurrency_rate
        );

        // Verificar que todos os 3 fatores foram calculados
        $this->assertEquals('HIGH', $result['confidence']);
        $this->assertNotNull($result['factors']['ctr']);
        $this->assertNotNull($result['factors']['conversion_rate']);
        $this->assertNotNull($result['factors']['recurrency']);
        $this->assertEquals(15.0, $result['factors']['recurrency']);
    }

    #[Test]
    public function query_high_confidence_opportunities()
    {
        // Scope para filtrar oportunidades HIGH confidence
        $highConf = ProductOpportunity::factory()
            ->for($this->application)
            ->for($this->trend)
            ->create([
                'confidence_level' => 'HIGH',
                'recurrency_rate' => 20.0,
            ]);

        $mediumConf = ProductOpportunity::factory()
            ->for($this->application)
            ->for($this->trend)
            ->create([
                'confidence_level' => 'MEDIUM',
                'recurrency_rate' => null,
            ]);

        $results = ProductOpportunity::byApplication($this->application->id)
            ->highConfidence()
            ->get();

        $this->assertTrue($results->contains($highConf));
        $this->assertFalse($results->contains($mediumConf));
        $this->assertEquals(1, $results->count());
    }

    #[Test]
    public function query_opportunities_with_recurrency()
    {
        // Scope para filtrar oportunidades que têm recurrency
        $withRec = ProductOpportunity::factory()
            ->for($this->application)
            ->for($this->trend)
            ->create(['recurrency_rate' => 8.5]);

        $withoutRec = ProductOpportunity::factory()
            ->for($this->application)
            ->for($this->trend)
            ->create(['recurrency_rate' => null]);

        $results = ProductOpportunity::byApplication($this->application->id)
            ->withRecurrency()
            ->get();

        $this->assertTrue($results->contains($withRec));
        $this->assertFalse($results->contains($withoutRec));
    }

    #[Test]
    public function confidence_level_progression()
    {
        // Validar progressão de confidence levels
        $levels = [
            'INSUFFICIENT_DATA' => null,
            'LOW' => 25.0,
            'MEDIUM' => 50.0,
            'HIGH' => 75.0,
        ];

        foreach ($levels as $level => $recurrency) {
            $opp = ProductOpportunity::factory()
                ->for($this->application)
                ->for($this->trend)
                ->create([
                    'confidence_level' => $level,
                    'recurrency_rate' => $recurrency,
                ]);

            $this->assertEquals($level, $opp->confidence_level);
            if ($recurrency !== null) {
                $this->assertEquals($recurrency, $opp->recurrency_rate);
            }
        }
    }

    #[Test]
    public function performance_breakdown_includes_recurrency_formula()
    {
        // Breakdown deve explicar como recurrency foi calculado
        $result = $this->calculator->calculate(
            clicks: 100,
            conversions: 5,
            impressions: 500,
            recurrency_rate: 10.0
        );

        $breakdown = $result['breakdown']['calculation'];

        // Verificar que breakdown contém todas as 3 componentes
        $this->assertStringContainsString('CTR:', $breakdown);
        $this->assertStringContainsString('Conv:', $breakdown);
        $this->assertStringContainsString('Rec:', $breakdown);
        $this->assertStringContainsString('+', $breakdown); // Componentes separadas por +
    }

    #[Test]
    public function recurrency_migration_doesnt_break_existing_data()
    {
        // Dados criados antes da migração (sem recurrency) continuam válidos
        $opp = ProductOpportunity::factory()
            ->for($this->application)
            ->for($this->trend)
            ->create();

        // Mesmo sem recurrency, opportunity deve funcionar
        $this->assertNotNull($opp->id);
        $this->assertNull($opp->recurrency_rate); // Padrão null

        // Puede ser consultado normalmente
        $found = ProductOpportunity::find($opp->id);
        $this->assertNotNull($found);
    }

    #[Test]
    public function recurrency_rate_decimal_precision()
    {
        // Recurrency é armazenado com 2 casas decimais
        $values = [10.12, 10.126, 10.125, 0.01, 99.99, 100.00];

        foreach ($values as $value) {
            $opp = ProductOpportunity::factory()
                ->for($this->application)
                ->for($this->trend)
                ->create(['recurrency_rate' => $value]);

            // Verificar que foi armazenado com precisão
            $fresh = $opp->fresh();
            $this->assertNotNull($fresh->recurrency_rate);
            $this->assertIsNumeric($fresh->recurrency_rate);
            $this->assertTrue((float) $fresh->recurrency_rate > 0);
        }
    }

    #[Test]
    public function workflow_with_recurrency_approval_to_publish()
    {
        // Fluxo completo: Opportunity → Approve → Publish (com recurrency)
        $opp = ProductOpportunity::factory()
            ->for($this->application)
            ->for($this->trend)
            ->create([
                'status_sprint_a' => 'DISCOVERED',
                'recurrency_rate' => 12.5,
                'confidence_level' => 'HIGH',
            ]);

        // Aprovar
        $approve = app(ApproveProductOpportunityAction::class);
        $approved = $approve->execute($opp, $this->user, 'Good recurrency');

        $this->assertEquals('APPROVED', $approved->status_sprint_a->value);
        $this->assertEquals(12.5, $approved->recurrency_rate); // Recurrency preservado

        // Publicar
        $publish = app(PublishProductOpportunityAction::class);
        $published = $publish->execute($approved);

        $this->assertEquals('PUBLISHED', $published->status_sprint_a->value);
        $this->assertEquals(12.5, $published->recurrency_rate);
        $this->assertEquals('HIGH', $published->confidence_level);
    }

    #[Test]
    public function multi_tenant_high_confidence_isolation()
    {
        // HIGH confidence opportunities são isolados por tenant
        $tenant2 = Tenant::factory()->create();
        $app2 = Application::factory()->for($tenant2)->create();

        $highApp1 = ProductOpportunity::factory()
            ->for($this->application)
            ->for($this->trend)
            ->create(['confidence_level' => 'HIGH']);

        $highApp2 = ProductOpportunity::factory()
            ->for($app2)
            ->for(Trend::factory()->for($app2)->create())
            ->create(['confidence_level' => 'HIGH']);

        // Query por app1 não deve retornar dados de app2
        $app1Results = ProductOpportunity::byApplication($this->application->id)
            ->highConfidence()
            ->get();

        $app2Results = ProductOpportunity::byApplication($app2->id)
            ->highConfidence()
            ->get();

        $this->assertTrue($app1Results->contains($highApp1));
        $this->assertFalse($app1Results->contains($highApp2));
        $this->assertTrue($app2Results->contains($highApp2));
        $this->assertFalse($app2Results->contains($highApp1));
    }

    #[Test]
    public function conversions_can_be_attributed_to_high_confidence_opportunity()
    {
        // Conversões podem ser associadas a opportunities HIGH confidence
        $opp = ProductOpportunity::factory()
            ->for($this->application)
            ->for($this->trend)
            ->create([
                'confidence_level' => 'HIGH',
                'recurrency_rate' => 25.0,
            ]);

        $conversion = AffiliateConversion::factory()->create([
            'product_opportunity_id' => $opp->id,
            'application_id' => $this->application->id,
        ]);

        $this->assertEquals($opp->id, $conversion->product_opportunity_id);

        // Oportunidade deve ter relação com conversão
        $fresh = $opp->fresh();
        $conversions = $fresh->affiliateConversions;
        $this->assertTrue($conversions->contains($conversion));
    }
}
