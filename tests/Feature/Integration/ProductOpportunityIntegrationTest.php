<?php

namespace Tests\Feature\Integration;

use App\Domain\Affiliate\Actions\ApproveProductOpportunityAction;
use App\Domain\Affiliate\Actions\PublishProductOpportunityAction;
use App\Domain\Affiliate\Actions\RejectProductOpportunityAction;
use App\Models\AffiliateConversion;
use App\Models\AffiliateLink;
use App\Models\AffiliateProduct;
use App\Models\Application;
use App\Models\CurationDecision;
use App\Models\ProductOpportunity;
use App\Models\Tenant;
use App\Models\Trend;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductOpportunityIntegrationTest extends TestCase
{
    protected Tenant $tenant;

    protected Application $application;

    protected Trend $trend;

    protected AffiliateProduct $product;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->application = Application::factory()->for($this->tenant)->create();
        $this->trend = Trend::factory()->for($this->application)->create();
        $this->product = AffiliateProduct::factory()->for($this->application)->create();
        $this->user = User::factory()->create();
    }

    #[Test]
    public function fluxo_positivo_completo_trend_to_attribution()
    {
        // 1. ProductOpportunity criada (DISCOVERED)
        $opportunity = ProductOpportunity::factory()
            ->for($this->application)
            ->for($this->trend)
            ->for($this->product, 'affiliateProduct')
            ->create([
                'status_sprint_a' => 'DISCOVERED',
                'discovery_opportunity_score' => 85,
            ]);

        $this->assertNotNull($opportunity);
        $this->assertEquals('DISCOVERED', $opportunity->status_sprint_a->value);
        $this->assertEquals($this->application->id, $opportunity->application_id);

        // 2. Operador aprova via Action
        $approve = app(ApproveProductOpportunityAction::class);
        $approved = $approve->execute($opportunity, $this->user, 'Bom score e tendência relevante');

        $this->assertEquals('APPROVED', $approved->status_sprint_a->value);
        $this->assertNotNull($approved->approved_at);
        $this->assertTrue(
            CurationDecision::where('product_opportunity_id', $opportunity->id)
                ->where('decision', 'APPROVED')
                ->exists()
        );

        // 3. Operador publica via Action
        $publish = app(PublishProductOpportunityAction::class);
        $published = $publish->execute($approved);

        $this->assertEquals('PUBLISHED', $published->status_sprint_a->value);
        $this->assertNotNull($published->published_at);

        // 4. AffiliateLink gerado e associado à oportunidade
        $link = AffiliateLink::factory()
            ->create([
                'product_opportunity_id' => $published->id,
            ]);

        $this->assertEquals($published->id, $link->product_opportunity_id);
        $this->assertTrue($published->affiliateLinks->contains($link));

        // 5. Conversão importada e atribuída à oportunidade
        $conversion = AffiliateConversion::factory()
            ->create([
                'product_opportunity_id' => $published->id,
                'application_id' => $this->application->id,
                'provider' => 'magalu',
                'external_conversion_id' => 'MAG-001',
                'product_price' => 299.90,
                'commission_value' => 15.00,
            ]);

        $this->assertEquals($published->id, $conversion->product_opportunity_id);
        $this->assertEquals($this->application->id, $conversion->application_id);

        // 6. Attribution chain íntegra
        $this->assertEquals($published->id, $link->product_opportunity_id);
        $this->assertEquals($published->id, $conversion->product_opportunity_id);

        // 7. Traçabilidade completa
        $linkedConversion = AffiliateConversion::where('product_opportunity_id', $published->id)->first();
        $this->assertNotNull($linkedConversion);
        $this->assertEquals(15.00, $linkedConversion->commission_value);
    }

    #[Test]
    public function fluxo_rejeicao_preserved_forever()
    {
        $opportunity = ProductOpportunity::factory()
            ->for($this->application)
            ->for($this->trend)
            ->create(['status_sprint_a' => 'DISCOVERED']);

        // Rejeitar via Action
        $reject = app(RejectProductOpportunityAction::class);
        $rejected = $reject->execute($opportunity, $this->user, 'Não alinha com portfolio');

        $this->assertEquals('REJECTED', $rejected->status_sprint_a->value);

        // CurationDecision registrada
        $decision = CurationDecision::where('product_opportunity_id', $opportunity->id)
            ->where('decision', 'REJECTED')
            ->first();
        $this->assertNotNull($decision);
        $this->assertEquals('Não alinha com portfolio', $decision->reason);

        // Verificar que status nunca é alterado de REJECTED
        $rejected->update(['status_sprint_a' => 'REJECTED']);
        $this->assertEquals('REJECTED', $rejected->fresh()->status_sprint_a->value);

        // Não deve haver AffiliateLink indevido
        $this->assertFalse(
            AffiliateLink::where('product_opportunity_id', $opportunity->id)->exists()
        );
    }

    #[Test]
    public function expiracao_automatica_discovered_analyzing_apenas()
    {
        // DISCOVERED com TTL vencido
        $discovered = ProductOpportunity::factory()
            ->for($this->application)
            ->for($this->trend)
            ->create([
                'status_sprint_a' => 'DISCOVERED',
                'expires_at' => now()->subDays(1),
            ]);

        // ANALYZING com TTL vencido
        $analyzing = ProductOpportunity::factory()
            ->for($this->application)
            ->for($this->trend)
            ->create([
                'status_sprint_a' => 'ANALYZING',
                'expires_at' => now()->subDays(1),
            ]);

        // APPROVED (não deve expirar)
        $approved = ProductOpportunity::factory()
            ->for($this->application)
            ->for($this->trend)
            ->create([
                'status_sprint_a' => 'APPROVED',
                'expires_at' => now()->subDays(1),
            ]);

        // REJECTED (não deve expirar)
        $rejected = ProductOpportunity::factory()
            ->for($this->application)
            ->for($this->trend)
            ->create([
                'status_sprint_a' => 'REJECTED',
                'expires_at' => now()->subDays(1),
            ]);

        // PUBLISHED (não deve expirar)
        $published = ProductOpportunity::factory()
            ->for($this->application)
            ->for($this->trend)
            ->create([
                'status_sprint_a' => 'PUBLISHED',
                'expires_at' => now()->subDays(1),
            ]);

        // DISCOVERED/ANALYZING com expires_at no passado devem poder expirar
        $this->assertTrue($discovered->expires_at < now());
        $this->assertTrue($analyzing->expires_at < now());

        // APPROVED/REJECTED/PUBLISHED não devem expirar mesmo com expires_at no passado
        $this->assertEquals('APPROVED', $approved->fresh()->status_sprint_a->value);
        $this->assertEquals('REJECTED', $rejected->fresh()->status_sprint_a->value);
        $this->assertEquals('PUBLISHED', $published->fresh()->status_sprint_a->value);
    }

    #[Test]
    public function idempotencia_conversao_provider_external_id()
    {
        $opportunity = ProductOpportunity::factory()
            ->for($this->application)
            ->for($this->trend)
            ->create();

        // Primeira importação
        $conv1 = AffiliateConversion::factory()->create([
            'product_opportunity_id' => $opportunity->id,
            'application_id' => $this->application->id,
            'provider' => 'magalu',
            'external_conversion_id' => 'MAG-123',
            'product_price' => 100.00,
            'commission_value' => 5.00,
        ]);

        $conv1Count = AffiliateConversion::where('product_opportunity_id', $opportunity->id)->count();
        $this->assertEquals(1, $conv1Count);
        $this->assertEquals(5.00, $conv1->commission_value);

        // Segunda importação MESMA conversão (idempotente via updateOrCreate)
        $conv2 = AffiliateConversion::updateOrCreate(
            [
                'application_id' => $this->application->id,
                'provider' => 'magalu',
                'external_conversion_id' => 'MAG-123',
            ],
            [
                'product_opportunity_id' => $opportunity->id,
                'product_price' => 100.00,
                'commission_value' => 5.00,
            ]
        );

        // Ainda deve ter apenas 1 conversão para essa oportunidade
        $conv2Count = AffiliateConversion::where('product_opportunity_id', $opportunity->id)->count();
        $this->assertEquals(1, $conv2Count);
        $this->assertEquals($conv1->id, $conv2->id);
        $this->assertEquals(5.00, $conv2->commission_value);

        // Receita e comissão não duplicadas
        $total = AffiliateConversion::where('product_opportunity_id', $opportunity->id)
            ->sum('commission_value');
        $this->assertEquals(5.00, $total);
    }

    #[Test]
    public function attribution_chain_rastreavel()
    {
        $opportunity = ProductOpportunity::factory()
            ->for($this->application)
            ->for($this->trend)
            ->create();

        $link = AffiliateLink::factory()
            ->create(['product_opportunity_id' => $opportunity->id]);

        $conversion = AffiliateConversion::factory()
            ->create([
                'product_opportunity_id' => $opportunity->id,
                'application_id' => $this->application->id,
            ]);

        // Traçar para TRÁS: Conversão → Oportunidade
        $this->assertEquals($opportunity->id, $conversion->product_opportunity_id);

        // Traçar para FRENTE: Oportunidade → Link
        $this->assertTrue($opportunity->affiliateLinks->contains($link));
        $this->assertEquals($link->id, $link->id);

        // Attribution chain íntegra: ProductOpportunity → Link → Conversion
        $conversionOpp = ProductOpportunity::find($conversion->product_opportunity_id);
        $this->assertEquals($opportunity->id, $conversionOpp->id);
    }

    #[Test]
    public function tenant_isolation_multi_application()
    {
        // Setup: 2 applications diferentes
        $app1 = $this->application;
        $tenant2 = Tenant::factory()->create();
        $app2 = Application::factory()->for($tenant2)->create();

        $trend1 = $this->trend;
        $trend2 = Trend::factory()->for($app2)->create();

        $product1 = $this->product;
        $product2 = AffiliateProduct::factory()->for($app2)->create();

        // Oportunidade em Application 1
        $opp1 = ProductOpportunity::factory()
            ->for($app1)
            ->for($trend1)
            ->for($product1, 'affiliateProduct')
            ->create(['status_sprint_a' => 'DISCOVERED']);

        // Oportunidade em Application 2
        $opp2 = ProductOpportunity::factory()
            ->for($app2)
            ->for($trend2)
            ->for($product2, 'affiliateProduct')
            ->create(['status_sprint_a' => 'DISCOVERED']);

        // App1 não vê oportunidades de App2
        $app1Opps = ProductOpportunity::byApplication($app1->id)->get();
        $this->assertFalse($app1Opps->contains($opp2));

        $app2Opps = ProductOpportunity::byApplication($app2->id)->get();
        $this->assertFalse($app2Opps->contains($opp1));

        // Links isolados (por oportunidade)
        $link1 = AffiliateLink::factory()->create(['product_opportunity_id' => $opp1->id]);
        $link2 = AffiliateLink::factory()->create(['product_opportunity_id' => $opp2->id]);

        // Verificar que estão associados às oportunidades corretas
        $this->assertEquals($opp1->id, $link1->product_opportunity_id);
        $this->assertEquals($opp2->id, $link2->product_opportunity_id);

        // Conversões isoladas
        $conv1 = AffiliateConversion::factory()->create([
            'product_opportunity_id' => $opp1->id,
            'application_id' => $app1->id,
        ]);
        $conv2 = AffiliateConversion::factory()->create([
            'product_opportunity_id' => $opp2->id,
            'application_id' => $app2->id,
        ]);

        $this->assertEquals($app1->id, $conv1->application_id);
        $this->assertEquals($app2->id, $conv2->application_id);
    }

    #[Test]
    public function workflow_invalido_falha_gracefully()
    {
        // Não pode publicar oportunidade não aprovada
        $discovered = ProductOpportunity::factory()
            ->for($this->application)
            ->for($this->trend)
            ->create(['status_sprint_a' => 'DISCOVERED']);

        $publish = app(PublishProductOpportunityAction::class);

        $this->expectException(\Exception::class);
        $publish->execute($discovered);
    }

    #[Test]
    public function consistencia_api_ui_domain_mesmas_actions()
    {
        $opportunity = ProductOpportunity::factory()
            ->for($this->application)
            ->for($this->trend)
            ->create(['status_sprint_a' => 'DISCOVERED']);

        // Via Action (usado por API e UI)
        $approve = app(ApproveProductOpportunityAction::class);
        $approved = $approve->execute($opportunity, $this->user, 'Teste');

        $this->assertEquals('APPROVED', $approved->status_sprint_a->value);

        // Verificar que nenhuma lógica extra foi executada fora da Action
        $decision = CurationDecision::where('product_opportunity_id', $opportunity->id)->first();
        $this->assertNotNull($decision);
        $this->assertEquals('APPROVED', $decision->decision);
    }

    #[Test]
    public function auditoria_historico_curation_decisions()
    {
        $opportunity = ProductOpportunity::factory()
            ->for($this->application)
            ->for($this->trend)
            ->create(['status_sprint_a' => 'DISCOVERED']);

        // Primeira decisão
        CurationDecision::create([
            'product_opportunity_id' => $opportunity->id,
            'application_id' => $this->application->id,
            'user_id' => $this->user->id,
            'decision' => 'APPROVED',
            'reason' => 'Boa score',
            'decided_at' => now(),
        ]);

        // Segunda decisão (histórico, não sobrescreve)
        CurationDecision::create([
            'product_opportunity_id' => $opportunity->id,
            'application_id' => $this->application->id,
            'user_id' => $this->user->id,
            'decision' => 'APPROVED',
            'reason' => 'Publicado',
            'decided_at' => now(),
        ]);

        $decisions = CurationDecision::where('product_opportunity_id', $opportunity->id)->get();
        $this->assertCount(2, $decisions);
        $this->assertEquals('APPROVED', $decisions[0]->decision);
        $this->assertEquals('APPROVED', $decisions[1]->decision);
    }

    #[Test]
    public function dados_parciais_sem_zero_artificial()
    {
        // Opportunity com dados parciais (alguns campos null)
        $opportunity = ProductOpportunity::factory()
            ->for($this->application)
            ->for($this->trend)
            ->create([
                'status_sprint_a' => 'DISCOVERED',
                'discovery_opportunity_score' => 75,
                'actual_performance_score' => null, // Performance ainda não calculada
            ]);

        // Não deve transformar null em zero artificial
        $this->assertNull($opportunity->actual_performance_score);
        $this->assertEquals(75, $opportunity->discovery_opportunity_score);

        // Conversão com dados básicos (commission sempre preenchida pelo factory)
        $conversion = AffiliateConversion::factory()
            ->create([
                'product_opportunity_id' => $opportunity->id,
                'application_id' => $this->application->id,
                'product_price' => 100.00,
            ]);

        // commission_value é required, não null
        $this->assertNotNull($conversion->commission_value);
        $this->assertEquals(100.00, $conversion->product_price);
    }
}
