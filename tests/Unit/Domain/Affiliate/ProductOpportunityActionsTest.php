<?php

namespace Tests\Unit\Domain\Affiliate;

use App\Domain\Affiliate\Actions\ApproveProductOpportunityAction;
use App\Domain\Affiliate\Actions\CreateProductOpportunityAction;
use App\Domain\Affiliate\Actions\PublishProductOpportunityAction;
use App\Domain\Affiliate\Actions\RejectProductOpportunityAction;
use App\Domain\Affiliate\Enums\StatusSprintA;
use App\Models\AffiliateProduct;
use App\Models\Application;
use App\Models\CurationDecision;
use App\Models\ProductOpportunity;
use App\Models\Tenant;
use App\Models\Trend;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductOpportunityActionsTest extends TestCase
{
    #[Test]
    public function create_action_creates_opportunity()
    {
        $tenant = Tenant::factory()->create();
        $app = Application::factory()->for($tenant)->create();
        $trend = Trend::factory()->for($app)->create();
        $product = AffiliateProduct::factory()->for($app)->create();

        $action = app(CreateProductOpportunityAction::class);
        $opportunity = $action->execute(
            applicationId: $app->id,
            trend: $trend,
            affiliateProduct: $product,
            discoveryOpportunityScore: 75,
            opportunityScoreBreakdown: ['trend' => 80, 'match' => 70],
            purchaseIntentScore: 85,
            purchaseIntentLabel: 'HIGH',
            purchaseIntentBreakdown: ['base' => 'TRANSACTIONAL'],
        );

        $this->assertInstanceOf(ProductOpportunity::class, $opportunity);
        $this->assertEquals($app->id, $opportunity->application_id);
        $this->assertEquals(StatusSprintA::DISCOVERED, $opportunity->status_sprint_a);
        $this->assertEquals(75, $opportunity->discovery_opportunity_score);
        $this->assertNotNull($opportunity->expires_at);
    }

    #[Test]
    public function approve_action_approves_opportunity()
    {
        $tenant = Tenant::factory()->create();
        $app = Application::factory()->for($tenant)->create();
        $trend = Trend::factory()->for($app)->create();
        $user = User::factory()->create();

        $opportunity = ProductOpportunity::factory()
            ->for($app)
            ->for($trend)
            ->create(['status_sprint_a' => StatusSprintA::DISCOVERED]);

        $action = new ApproveProductOpportunityAction();
        $approved = $action->execute(
            opportunity: $opportunity,
            approver: $user,
            reason: 'Bom produto em alta',
        );

        $this->assertEquals(StatusSprintA::APPROVED, $approved->status_sprint_a);
        $this->assertNotNull($approved->approved_at);

        $decision = CurationDecision::where('product_opportunity_id', $opportunity->id)->first();
        $this->assertNotNull($decision);
        $this->assertEquals(CurationDecision::DECISION_APPROVED, $decision->decision);
        $this->assertEquals('Bom produto em alta', $decision->reason);
    }

    #[Test]
    public function reject_action_rejects_opportunity()
    {
        $tenant = Tenant::factory()->create();
        $app = Application::factory()->for($tenant)->create();
        $trend = Trend::factory()->for($app)->create();
        $user = User::factory()->create();

        $opportunity = ProductOpportunity::factory()
            ->for($app)
            ->for($trend)
            ->create(['status_sprint_a' => StatusSprintA::DISCOVERED]);

        $action = new RejectProductOpportunityAction();
        $rejected = $action->execute(
            opportunity: $opportunity,
            rejecter: $user,
            reason: 'Marca não confiável',
        );

        $this->assertEquals(StatusSprintA::REJECTED, $rejected->status_sprint_a);

        $decision = CurationDecision::where('product_opportunity_id', $opportunity->id)->first();
        $this->assertNotNull($decision);
        $this->assertEquals(CurationDecision::DECISION_REJECTED, $decision->decision);
        $this->assertEquals('Marca não confiável', $decision->reason);
    }

    #[Test]
    public function publish_action_publishes_approved_opportunity()
    {
        $tenant = Tenant::factory()->create();
        $app = Application::factory()->for($tenant)->create();
        $trend = Trend::factory()->for($app)->create();

        $opportunity = ProductOpportunity::factory()
            ->for($app)
            ->for($trend)
            ->create(['status_sprint_a' => StatusSprintA::APPROVED]);

        $action = new PublishProductOpportunityAction();
        $published = $action->execute($opportunity);

        $this->assertEquals(StatusSprintA::PUBLISHED, $published->status_sprint_a);
        $this->assertNotNull($published->published_at);
    }

    #[Test]
    public function publish_action_fails_for_unapproved()
    {
        $tenant = Tenant::factory()->create();
        $app = Application::factory()->for($tenant)->create();
        $trend = Trend::factory()->for($app)->create();

        $opportunity = ProductOpportunity::factory()
            ->for($app)
            ->for($trend)
            ->create(['status_sprint_a' => StatusSprintA::DISCOVERED]);

        $action = new PublishProductOpportunityAction();

        $this->expectException(\InvalidArgumentException::class);
        $action->execute($opportunity);
    }
}
