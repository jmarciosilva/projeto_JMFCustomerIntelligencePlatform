<?php

namespace Tests\Feature\Intelligence;

use App\Actions\GenerateBusinessRecommendationsAction;
use App\Domain\Intelligence\BusinessAdvisor;
use App\Models\Application;
use App\Models\BusinessRecommendation;
use App\Models\MarketplaceMetric;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateBusinessRecommendationsActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_action_persists_recommendations(): void
    {
        $app = Application::factory()->create();

        MarketplaceMetric::create([
            'tenant_id' => $app->tenant_id, 'application_id' => $app->id, 'seller_id' => 1,
            'product_id' => 100, 'date' => now()->subDays(10)->toDateString(), 'purchases' => 20,
        ]);
        MarketplaceMetric::create([
            'tenant_id' => $app->tenant_id, 'application_id' => $app->id, 'seller_id' => 1,
            'product_id' => 100, 'date' => now()->subDays(2)->toDateString(), 'purchases' => 5,
        ]);

        $action = new GenerateBusinessRecommendationsAction(new BusinessAdvisor);
        $generated = $action->execute($app->id);

        $this->assertGreaterThan(0, $generated);
        $this->assertDatabaseHas('business_recommendations', [
            'application_id' => $app->id,
            'seller_id' => 1,
            'type' => BusinessRecommendation::TYPE_SALES_DROP,
        ]);
    }

    public function test_action_clears_stale_recommendations_before_recomputing(): void
    {
        $app = Application::factory()->create();

        BusinessRecommendation::create([
            'application_id' => $app->id, 'seller_id' => 1, 'type' => BusinessRecommendation::TYPE_SALES_DROP,
            'priority' => 50, 'title' => 'stale', 'message' => 'stale', 'generated_at' => now()->subDays(3),
        ]);

        $action = new GenerateBusinessRecommendationsAction(new BusinessAdvisor);
        $action->execute($app->id);

        $this->assertDatabaseMissing('business_recommendations', ['title' => 'stale']);
    }

    public function test_action_isolated_by_application_filter(): void
    {
        $app1 = Application::factory()->create();
        $app2 = Application::factory()->create();

        MarketplaceMetric::create([
            'tenant_id' => $app1->tenant_id, 'application_id' => $app1->id, 'seller_id' => 1,
            'product_id' => 100, 'date' => now()->subDays(10)->toDateString(), 'purchases' => 20,
        ]);
        MarketplaceMetric::create([
            'tenant_id' => $app1->tenant_id, 'application_id' => $app1->id, 'seller_id' => 1,
            'product_id' => 100, 'date' => now()->subDays(2)->toDateString(), 'purchases' => 5,
        ]);

        $action = new GenerateBusinessRecommendationsAction(new BusinessAdvisor);
        $action->execute($app1->id);

        $this->assertDatabaseHas('business_recommendations', ['application_id' => $app1->id]);
        $this->assertDatabaseMissing('business_recommendations', ['application_id' => $app2->id]);
    }
}
