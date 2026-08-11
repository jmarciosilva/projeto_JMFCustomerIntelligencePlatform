<?php

namespace Tests\Feature;

use App\Domain\Affiliate\Enums\StatusSprintA;
use App\Models\AffiliateProduct;
use App\Models\Application;
use App\Models\ProductOpportunity;
use App\Models\Tenant;
use App\Models\Trend;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductOpportunityApiTest extends TestCase
{
    private Application $application;

    private User $user;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $tenant = Tenant::factory()->create();
        $this->application = Application::factory()->for($tenant)->create();
        $this->user = User::factory()->create();
        $this->token = $this->application->createToken('test')->plainTextToken;
    }

    private function withAuth()
    {
        return $this->withHeader('Authorization', "Bearer {$this->token}");
    }

    #[Test]
    public function list_product_opportunities()
    {
        $trend = Trend::factory()->for($this->application)->create();
        ProductOpportunity::factory(3)
            ->for($this->application)
            ->for($trend)
            ->create(['status_sprint_a' => StatusSprintA::DISCOVERED]);

        $response = $this->withAuth()
            ->getJson('/api/v1/affiliate/product-opportunities');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'application_id', 'status', 'purchase_intent'],
                ],
                'links',
                'meta',
            ]);
    }

    #[Test]
    public function show_product_opportunity()
    {
        $trend = Trend::factory()->for($this->application)->create();
        $opportunity = ProductOpportunity::factory()
            ->for($this->application)
            ->for($trend)
            ->create();

        $response = $this->withAuth()
            ->getJson("/api/v1/affiliate/product-opportunities/{$opportunity->id}");

        $response->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('data.id', $opportunity->id)
                ->where('data.status', StatusSprintA::DISCOVERED->value)
                ->has('data.purchase_intent')
                ->has('data.lifecycle')
            );
    }

    #[Test]
    public function create_product_opportunity()
    {
        $trend = Trend::factory()->for($this->application)->create();
        $product = AffiliateProduct::factory()->for($this->application)->create();

        $response = $this->withAuth()
            ->postJson('/api/v1/affiliate/product-opportunities', [
                'trend_id' => $trend->id,
                'affiliate_product_id' => $product->id,
                'discovery_opportunity_score' => 75,
                'purchase_intent_score' => 80,
                'purchase_intent_label' => 'HIGH',
            ]);

        $response->assertStatus(201)
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('data.status', StatusSprintA::DISCOVERED->value)
                ->where('data.discovery_opportunity_score', 75)
            );

        $this->assertDatabaseHas('product_opportunities', [
            'trend_id' => $trend->id,
            'application_id' => $this->application->id,
        ]);
    }

    #[Test]
    public function approve_product_opportunity()
    {
        $trend = Trend::factory()->for($this->application)->create();
        $opportunity = ProductOpportunity::factory()
            ->for($this->application)
            ->for($trend)
            ->create(['status_sprint_a' => StatusSprintA::DISCOVERED]);

        $response = $this->withAuth()
            ->patchJson(
                "/api/v1/affiliate/product-opportunities/{$opportunity->id}/approve",
                ['reason' => 'Bom para divulgar']
            );

        $response->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('data.status', StatusSprintA::APPROVED->value)
            );

        $opportunity->refresh();
        $this->assertEquals(StatusSprintA::APPROVED, $opportunity->status_sprint_a);
    }

    #[Test]
    public function reject_product_opportunity()
    {
        $trend = Trend::factory()->for($this->application)->create();
        $opportunity = ProductOpportunity::factory()
            ->for($this->application)
            ->for($trend)
            ->create(['status_sprint_a' => StatusSprintA::DISCOVERED]);

        $response = $this->withAuth()
            ->patchJson(
                "/api/v1/affiliate/product-opportunities/{$opportunity->id}/reject",
                ['reason' => 'Marca não confiável']
            );

        $response->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('data.status', StatusSprintA::REJECTED->value)
            );

        $opportunity->refresh();
        $this->assertEquals(StatusSprintA::REJECTED, $opportunity->status_sprint_a);
    }

    #[Test]
    public function publish_product_opportunity()
    {
        $trend = Trend::factory()->for($this->application)->create();
        $opportunity = ProductOpportunity::factory()
            ->for($this->application)
            ->for($trend)
            ->create(['status_sprint_a' => StatusSprintA::APPROVED]);

        $response = $this->withAuth()
            ->patchJson("/api/v1/affiliate/product-opportunities/{$opportunity->id}/publish");

        $response->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) => $json
                ->where('data.status', StatusSprintA::PUBLISHED->value)
            );

        $opportunity->refresh();
        $this->assertEquals(StatusSprintA::PUBLISHED, $opportunity->status_sprint_a);
    }

    #[Test]
    public function cannot_access_other_application_opportunity()
    {
        $tenant2 = Tenant::factory()->create();
        $app2 = Application::factory()->for($tenant2)->create();
        $trend = Trend::factory()->for($app2)->create();
        $opportunity = ProductOpportunity::factory()
            ->for($app2)
            ->for($trend)
            ->create();

        $response = $this->withAuth()
            ->getJson("/api/v1/affiliate/product-opportunities/{$opportunity->id}");

        $response->assertStatus(403);
    }
}
