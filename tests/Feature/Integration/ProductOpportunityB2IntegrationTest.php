<?php

namespace Tests\Feature\Integration;

use App\Domain\Affiliate\Actions\CreateProductOpportunityAction;
use App\Domain\Affiliate\RecurrencyCalculator;
use App\Models\AffiliateConversion;
use App\Models\Application;
use App\Models\AffiliateProduct;
use App\Models\ProductOpportunity;
use App\Models\Tenant;
use App\Models\Trend;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductOpportunityB2IntegrationTest extends TestCase
{
    protected Tenant $tenant;
    protected Application $application;
    protected Trend $trend;
    protected AffiliateProduct $product;
    protected RecurrencyCalculator $calculator;
    protected CreateProductOpportunityAction $createAction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->application = Application::factory()->for($this->tenant)->create();
        $this->trend = Trend::factory()->for($this->application)->create();
        $this->product = AffiliateProduct::factory()->for($this->application)->create();
        $this->calculator = app(RecurrencyCalculator::class);
        $this->createAction = app(CreateProductOpportunityAction::class);
    }

    #[Test]
    public function create_opportunity_without_contact_id_calculates_null_recurrency()
    {
        // Quando nenhum contact_id fornecido, recurrency_rate = null
        $opp = $this->createAction->execute(
            applicationId: $this->application->id,
            trend: $this->trend,
            affiliateProduct: $this->product,
            discoveryOpportunityScore: 75,
            opportunityScoreBreakdown: ['score' => 75],
            purchaseIntentScore: 60,
            purchaseIntentLabel: 'HIGH',
            purchaseIntentBreakdown: ['intent' => 'high'],
            contactId: null, // Sem contact
        );

        $this->assertNull($opp->recurrency_rate);
        $this->assertEquals('MEDIUM', $opp->confidence_level); // Sem recurrency = MEDIUM
    }

    #[Test]
    public function create_opportunity_with_contact_without_conversions_null_recurrency()
    {
        // Contact sem histórico de conversões → recurrency_rate = null
        $opp = $this->createAction->execute(
            applicationId: $this->application->id,
            trend: $this->trend,
            affiliateProduct: $this->product,
            discoveryOpportunityScore: 75,
            opportunityScoreBreakdown: ['score' => 75],
            purchaseIntentScore: 60,
            purchaseIntentLabel: 'HIGH',
            purchaseIntentBreakdown: ['intent' => 'high'],
            contactId: 999, // Contact ID fictício sem conversões
        );

        $this->assertNull($opp->recurrency_rate);
        $this->assertEquals('MEDIUM', $opp->confidence_level);
    }

    #[Test]
    public function create_opportunity_with_contact_with_conversions_calculates_recurrency()
    {
        // Contact com histórico de conversões → recurrency_rate calculado
        $contactId = 12345;

        // Criar 8 conversões nos últimos 90 dias para este contact
        for ($i = 0; $i < 8; $i++) {
            AffiliateConversion::factory()->create([
                'application_id' => $this->application->id,
                'contact_id' => $contactId,
                'created_at' => now()->subDays(rand(1, 80)), // Últimos 80 dias
            ]);
        }

        $opp = $this->createAction->execute(
            applicationId: $this->application->id,
            trend: $this->trend,
            affiliateProduct: $this->product,
            discoveryOpportunityScore: 75,
            opportunityScoreBreakdown: ['score' => 75],
            purchaseIntentScore: 60,
            purchaseIntentLabel: 'HIGH',
            purchaseIntentBreakdown: ['intent' => 'high'],
            contactId: $contactId,
        );

        // 8 conversões / 90 dias = ~8.89%
        $this->assertNotNull($opp->recurrency_rate);
        $this->assertEqualsWithDelta(8.89, $opp->recurrency_rate, 0.1);
        $this->assertEquals('HIGH', $opp->confidence_level); // 3 fatores = HIGH
    }

    #[Test]
    public function recurrency_rate_high_confidence_determines_level()
    {
        // Com recurrency → confidence_level = HIGH
        $contactId = 67890;

        // 45 conversões = 50% recurrency
        for ($i = 0; $i < 45; $i++) {
            AffiliateConversion::factory()->create([
                'application_id' => $this->application->id,
                'contact_id' => $contactId,
                'created_at' => now()->subDays(rand(1, 85)),
            ]);
        }

        $opp = $this->createAction->execute(
            applicationId: $this->application->id,
            trend: $this->trend,
            affiliateProduct: $this->product,
            discoveryOpportunityScore: 80,
            opportunityScoreBreakdown: ['score' => 80],
            purchaseIntentScore: 70,
            purchaseIntentLabel: 'HIGH',
            purchaseIntentBreakdown: ['intent' => 'high'],
            contactId: $contactId,
        );

        $this->assertEquals(50.0, $opp->recurrency_rate);
        $this->assertEquals('HIGH', $opp->confidence_level);
    }

    #[Test]
    public function opportunity_creation_persists_recurrency_and_confidence()
    {
        $contactId = 11111;

        // 9 conversões = 10% recurrency
        for ($i = 0; $i < 9; $i++) {
            AffiliateConversion::factory()->create([
                'application_id' => $this->application->id,
                'contact_id' => $contactId,
                'created_at' => now()->subDays(rand(1, 75)),
            ]);
        }

        $opp = $this->createAction->execute(
            applicationId: $this->application->id,
            trend: $this->trend,
            affiliateProduct: $this->product,
            discoveryOpportunityScore: 70,
            opportunityScoreBreakdown: ['score' => 70],
            purchaseIntentScore: 55,
            purchaseIntentLabel: 'MEDIUM',
            purchaseIntentBreakdown: ['intent' => 'medium'],
            contactId: $contactId,
        );

        // Verificar que dados foram persistidos
        $fresh = ProductOpportunity::find($opp->id);
        $this->assertEquals(10.0, $fresh->recurrency_rate);
        $this->assertEquals('HIGH', $fresh->confidence_level);
    }

    #[Test]
    public function multiple_contacts_isolated_recurrency()
    {
        $contact1 = 10001;
        $contact2 = 10002;

        // Contact1: 5 conversões = 5.56% recurrency
        for ($i = 0; $i < 5; $i++) {
            AffiliateConversion::factory()->create([
                'application_id' => $this->application->id,
                'contact_id' => $contact1,
                'created_at' => now()->subDays(rand(1, 70)),
            ]);
        }

        // Contact2: 20 conversões = 22.22% recurrency
        for ($i = 0; $i < 20; $i++) {
            AffiliateConversion::factory()->create([
                'application_id' => $this->application->id,
                'contact_id' => $contact2,
                'created_at' => now()->subDays(rand(1, 70)),
            ]);
        }

        // Oportunidade para contact1
        $opp1 = $this->createAction->execute(
            applicationId: $this->application->id,
            trend: $this->trend,
            affiliateProduct: $this->product,
            discoveryOpportunityScore: 50,
            opportunityScoreBreakdown: ['score' => 50],
            purchaseIntentScore: 40,
            purchaseIntentLabel: 'LOW',
            purchaseIntentBreakdown: ['intent' => 'low'],
            contactId: $contact1,
        );

        // Oportunidade para contact2
        $opp2 = $this->createAction->execute(
            applicationId: $this->application->id,
            trend: Trend::factory()->for($this->application)->create(),
            affiliateProduct: AffiliateProduct::factory()->for($this->application)->create(),
            discoveryOpportunityScore: 60,
            opportunityScoreBreakdown: ['score' => 60],
            purchaseIntentScore: 50,
            purchaseIntentLabel: 'MEDIUM',
            purchaseIntentBreakdown: ['intent' => 'medium'],
            contactId: $contact2,
        );

        // Verificar isolamento
        $this->assertEqualsWithDelta(5.56, $opp1->recurrency_rate, 0.1);
        $this->assertEqualsWithDelta(22.22, $opp2->recurrency_rate, 0.1);
    }

    #[Test]
    public function recurrency_only_counts_last_90_days()
    {
        $contactId = 22222;

        // 5 conversões nos últimos 80 dias
        for ($i = 0; $i < 5; $i++) {
            AffiliateConversion::factory()->create([
                'application_id' => $this->application->id,
                'contact_id' => $contactId,
                'created_at' => now()->subDays(rand(1, 80)), // < 90 dias
            ]);
        }

        // 10 conversões há 100+ dias atrás (não devem contar)
        for ($i = 0; $i < 10; $i++) {
            AffiliateConversion::factory()->create([
                'application_id' => $this->application->id,
                'contact_id' => $contactId,
                'created_at' => now()->subDays(rand(100, 150)), // > 90 dias
            ]);
        }

        $opp = $this->createAction->execute(
            applicationId: $this->application->id,
            trend: $this->trend,
            affiliateProduct: $this->product,
            discoveryOpportunityScore: 65,
            opportunityScoreBreakdown: ['score' => 65],
            purchaseIntentScore: 50,
            purchaseIntentLabel: 'MEDIUM',
            purchaseIntentBreakdown: ['intent' => 'medium'],
            contactId: $contactId,
        );

        // Apenas 5 conversões contam (não as 10 antigas)
        $this->assertEqualsWithDelta(5.56, $opp->recurrency_rate, 0.1);
    }

    #[Test]
    public function multi_tenant_recurrency_isolation()
    {
        $tenant2 = Tenant::factory()->create();
        $app2 = Application::factory()->for($tenant2)->create();

        $contactId = 33333;

        // Contact em app1 com 8 conversões
        for ($i = 0; $i < 8; $i++) {
            AffiliateConversion::factory()->create([
                'application_id' => $this->application->id,
                'contact_id' => $contactId,
                'created_at' => now()->subDays(rand(1, 80)),
            ]);
        }

        // Mesmo contact em app2 com 25 conversões (isolated)
        for ($i = 0; $i < 25; $i++) {
            AffiliateConversion::factory()->create([
                'application_id' => $app2->id,
                'contact_id' => $contactId,
                'created_at' => now()->subDays(rand(1, 80)),
            ]);
        }

        // Oportunidade em app1
        $opp1 = $this->createAction->execute(
            applicationId: $this->application->id,
            trend: $this->trend,
            affiliateProduct: $this->product,
            discoveryOpportunityScore: 70,
            opportunityScoreBreakdown: ['score' => 70],
            purchaseIntentScore: 60,
            purchaseIntentLabel: 'HIGH',
            purchaseIntentBreakdown: ['intent' => 'high'],
            contactId: $contactId,
        );

        // Oportunidade em app2
        $opp2 = $this->createAction->execute(
            applicationId: $app2->id,
            trend: Trend::factory()->for($app2)->create(),
            affiliateProduct: AffiliateProduct::factory()->for($app2)->create(),
            discoveryOpportunityScore: 75,
            opportunityScoreBreakdown: ['score' => 75],
            purchaseIntentScore: 65,
            purchaseIntentLabel: 'HIGH',
            purchaseIntentBreakdown: ['intent' => 'high'],
            contactId: $contactId,
        );

        // App1: 8 conversões = 8.89%
        $this->assertEqualsWithDelta(8.89, $opp1->recurrency_rate, 0.1);

        // App2: 25 conversões = 27.78%
        $this->assertEqualsWithDelta(27.78, $opp2->recurrency_rate, 0.1);
    }

    #[Test]
    public function recurrency_100_when_many_conversions()
    {
        $contactId = 44444;

        // 100 conversões = 111% → capped at 100
        for ($i = 0; $i < 100; $i++) {
            AffiliateConversion::factory()->create([
                'application_id' => $this->application->id,
                'contact_id' => $contactId,
                'created_at' => now()->subDays(rand(1, 85)),
            ]);
        }

        $opp = $this->createAction->execute(
            applicationId: $this->application->id,
            trend: $this->trend,
            affiliateProduct: $this->product,
            discoveryOpportunityScore: 90,
            opportunityScoreBreakdown: ['score' => 90],
            purchaseIntentScore: 80,
            purchaseIntentLabel: 'HIGH',
            purchaseIntentBreakdown: ['intent' => 'high'],
            contactId: $contactId,
        );

        $this->assertEquals(100.0, $opp->recurrency_rate);
    }
}
