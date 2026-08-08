<?php

namespace Tests\Feature\Intelligence;

use App\Actions\AnalyzeTrendsAction;
use App\Domain\Intelligence\TrendAnalyzer;
use App\Models\Application;
use App\Models\MarketplaceMetric;
use App\Models\ProductTrend;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrendAnalyzerTest extends TestCase
{
    use RefreshDatabase;

    private TrendAnalyzer $analyzer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->analyzer = new TrendAnalyzer;
    }

    private function createMetric(Application $app, int $productId, string $date, int $views, int $purchases = 0, float $revenue = 0): MarketplaceMetric
    {
        return MarketplaceMetric::create([
            'tenant_id' => $app->tenant_id,
            'application_id' => $app->id,
            'seller_id' => 1,
            'product_id' => $productId,
            'date' => $date,
            'product_views' => $views,
            'purchases' => $purchases,
            'revenue' => $revenue,
        ]);
    }

    public function test_rising_trend_detected_correctly(): void
    {
        $tenant = Tenant::factory()->create();
        $app = Application::factory()->for($tenant)->create();

        // Previous period (8-14 days ago): 10 views
        $this->createMetric($app, 100, now()->subDays(10)->toDateString(), 10);
        // Current period (0-7 days ago): 30 views (200% growth)
        $this->createMetric($app, 100, now()->subDays(2)->toDateString(), 30);

        $trends = $this->analyzer->analyze($app->id, 7);
        $trend = $trends->firstWhere('product_id', 100);

        $this->assertEquals(ProductTrend::DIRECTION_RISING, $trend['direction']);
        $this->assertGreaterThan(0, $trend['growth_rate']);
    }

    public function test_falling_trend_detected_correctly(): void
    {
        $tenant = Tenant::factory()->create();
        $app = Application::factory()->for($tenant)->create();

        $this->createMetric($app, 200, now()->subDays(10)->toDateString(), 100);
        $this->createMetric($app, 200, now()->subDays(2)->toDateString(), 20);

        $trends = $this->analyzer->analyze($app->id, 7);
        $trend = $trends->firstWhere('product_id', 200);

        $this->assertEquals(ProductTrend::DIRECTION_FALLING, $trend['direction']);
        $this->assertLessThan(0, $trend['growth_rate']);
    }

    public function test_stable_trend_when_no_significant_change(): void
    {
        $tenant = Tenant::factory()->create();
        $app = Application::factory()->for($tenant)->create();

        $this->createMetric($app, 300, now()->subDays(10)->toDateString(), 50);
        $this->createMetric($app, 300, now()->subDays(2)->toDateString(), 52);

        $trends = $this->analyzer->analyze($app->id, 7);
        $trend = $trends->firstWhere('product_id', 300);

        $this->assertEquals(ProductTrend::DIRECTION_STABLE, $trend['direction']);
    }

    public function test_new_product_with_no_previous_data_is_rising(): void
    {
        $tenant = Tenant::factory()->create();
        $app = Application::factory()->for($tenant)->create();

        $this->createMetric($app, 400, now()->subDays(1)->toDateString(), 15);

        $trends = $this->analyzer->analyze($app->id, 7);
        $trend = $trends->firstWhere('product_id', 400);

        $this->assertEquals(ProductTrend::DIRECTION_RISING, $trend['direction']);
        $this->assertEquals(100.0, $trend['growth_rate']);
    }

    public function test_analyze_trends_action_persists_records(): void
    {
        $tenant = Tenant::factory()->create();
        $app = Application::factory()->for($tenant)->create();

        $this->createMetric($app, 500, now()->subDays(10)->toDateString(), 10);
        $this->createMetric($app, 500, now()->subDays(2)->toDateString(), 40);

        $action = new AnalyzeTrendsAction($this->analyzer);
        $updated = $action->execute($app->id);

        $this->assertGreaterThan(0, $updated);
        $this->assertDatabaseHas('product_trends', [
            'application_id' => $app->id,
            'product_id' => 500,
            'direction' => ProductTrend::DIRECTION_RISING,
        ]);
    }

    public function test_trends_are_isolated_by_application(): void
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();
        $app1 = Application::factory()->for($tenant1)->create();
        $app2 = Application::factory()->for($tenant2)->create();

        $this->createMetric($app1, 600, now()->subDays(2)->toDateString(), 20);
        $this->createMetric($app2, 700, now()->subDays(2)->toDateString(), 20);

        $trends = $this->analyzer->analyze($app1->id, 7);

        $this->assertNull($trends->firstWhere('product_id', 700));
        $this->assertNotNull($trends->firstWhere('product_id', 600));
    }
}
