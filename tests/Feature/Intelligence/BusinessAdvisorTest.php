<?php

namespace Tests\Feature\Intelligence;

use App\Domain\Intelligence\BusinessAdvisor;
use App\Models\Application;
use App\Models\BusinessRecommendation;
use App\Models\Event;
use App\Models\MarketplaceMetric;
use App\Models\Opportunity;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessAdvisorTest extends TestCase
{
    use RefreshDatabase;

    private BusinessAdvisor $advisor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->advisor = new BusinessAdvisor;
    }

    private function createMetric(Application $app, int $sellerId, int $productId, string $date, int $purchases): MarketplaceMetric
    {
        return MarketplaceMetric::create([
            'tenant_id' => $app->tenant_id,
            'application_id' => $app->id,
            'seller_id' => $sellerId,
            'product_id' => $productId,
            'date' => $date,
            'purchases' => $purchases,
        ]);
    }

    public function test_sales_drop_detected_for_significant_decline(): void
    {
        $tenant = Tenant::factory()->create();
        $app = Application::factory()->for($tenant)->create();

        $this->createMetric($app, 1, 100, now()->subDays(10)->toDateString(), 20);
        $this->createMetric($app, 1, 100, now()->subDays(2)->toDateString(), 5);

        $recommendations = $this->advisor->detectSalesDrops($app->id);

        $this->assertCount(1, $recommendations);
        $this->assertEquals(BusinessRecommendation::TYPE_SALES_DROP, $recommendations->first()['type']);
        $this->assertEquals(1, $recommendations->first()['seller_id']);
    }

    public function test_sales_drop_not_flagged_for_minor_change(): void
    {
        $tenant = Tenant::factory()->create();
        $app = Application::factory()->for($tenant)->create();

        $this->createMetric($app, 1, 100, now()->subDays(10)->toDateString(), 20);
        $this->createMetric($app, 1, 100, now()->subDays(2)->toDateString(), 19);

        $recommendations = $this->advisor->detectSalesDrops($app->id);

        $this->assertCount(0, $recommendations);
    }

    public function test_sales_drop_skipped_when_no_previous_data(): void
    {
        $tenant = Tenant::factory()->create();
        $app = Application::factory()->for($tenant)->create();

        $this->createMetric($app, 1, 100, now()->subDays(2)->toDateString(), 5);

        $recommendations = $this->advisor->detectSalesDrops($app->id);

        $this->assertCount(0, $recommendations);
    }

    public function test_kit_opportunity_attributes_seller_from_marketplace_metric(): void
    {
        $tenant = Tenant::factory()->create();
        $app = Application::factory()->for($tenant)->create();

        $this->createMetric($app, 7, 10, now()->subDays(1)->toDateString(), 1);

        Opportunity::create([
            'application_id' => $app->id,
            'type' => Opportunity::TYPE_BUNDLE,
            'product_id' => 10,
            'related_product_id' => 20,
            'score' => 90,
            'reason' => 'strong affinity',
            'detected_at' => now(),
        ]);

        $recommendations = $this->advisor->detectKitOpportunities($app->id);

        $this->assertCount(1, $recommendations);
        $this->assertEquals(7, $recommendations->first()['seller_id']);
    }

    public function test_kit_opportunity_skipped_without_seller_attribution(): void
    {
        $tenant = Tenant::factory()->create();
        $app = Application::factory()->for($tenant)->create();

        Opportunity::create([
            'application_id' => $app->id,
            'type' => Opportunity::TYPE_BUNDLE,
            'product_id' => 999,
            'related_product_id' => 998,
            'score' => 90,
            'reason' => 'strong affinity',
            'detected_at' => now(),
        ]);

        $recommendations = $this->advisor->detectKitOpportunities($app->id);

        $this->assertCount(0, $recommendations);
    }

    private function createProductViewEvent(Application $app, int $productId, string $category, float $price, int $sellerId): Event
    {
        return Event::factory()->create([
            'application_id' => $app->id,
            'event_name' => 'product.viewed',
            'properties' => ['product_id' => $productId, 'category' => $category, 'price' => $price, 'seller_id' => $sellerId],
            'occurred_at' => now(),
        ]);
    }

    public function test_price_outlier_detected_above_category_average(): void
    {
        $tenant = Tenant::factory()->create();
        $app = Application::factory()->for($tenant)->create();

        $this->createProductViewEvent($app, 1, 'Artesanato', 50, 1);
        $this->createProductViewEvent($app, 2, 'Artesanato', 50, 1);
        $this->createProductViewEvent($app, 3, 'Artesanato', 200, 2); // way above average

        $recommendations = $this->advisor->detectPriceOutliers($app->id);

        $outlier = $recommendations->firstWhere('data.product_id', 3);
        $this->assertNotNull($outlier);
        $this->assertEquals(BusinessRecommendation::TYPE_PRICE_OUTLIER, $outlier['type']);
        $this->assertGreaterThan(0, $outlier['data']['deviation']);
    }

    public function test_price_outlier_not_flagged_within_normal_range(): void
    {
        $tenant = Tenant::factory()->create();
        $app = Application::factory()->for($tenant)->create();

        $this->createProductViewEvent($app, 1, 'Artesanato', 50, 1);
        $this->createProductViewEvent($app, 2, 'Artesanato', 55, 1);

        $recommendations = $this->advisor->detectPriceOutliers($app->id);

        $this->assertCount(0, $recommendations);
    }

    private function createPurchaseEvent(Application $app, int $sellerId, string $occurredAt): Event
    {
        return Event::factory()->create([
            'application_id' => $app->id,
            'event_name' => 'purchase.completed',
            'properties' => ['seller_id' => $sellerId, 'total_value' => 50],
            'occurred_at' => $occurredAt,
        ]);
    }

    public function test_ideal_timing_identifies_peak_hour(): void
    {
        $tenant = Tenant::factory()->create();
        $app = Application::factory()->for($tenant)->create();

        // 5 purchases at 14h, 1 at 9h
        for ($i = 0; $i < 5; $i++) {
            $this->createPurchaseEvent($app, 1, now()->setTime(14, 0)->subDays($i)->toDateTimeString());
        }
        $this->createPurchaseEvent($app, 1, now()->setTime(9, 0)->toDateTimeString());

        $recommendations = $this->advisor->detectIdealTiming($app->id);

        $this->assertCount(1, $recommendations);
        $this->assertEquals(14, $recommendations->first()['data']['peak_hour']);
    }

    public function test_ideal_timing_skipped_with_insufficient_data(): void
    {
        $tenant = Tenant::factory()->create();
        $app = Application::factory()->for($tenant)->create();

        $this->createPurchaseEvent($app, 1, now()->toDateTimeString());

        $recommendations = $this->advisor->detectIdealTiming($app->id);

        $this->assertCount(0, $recommendations);
    }

    public function test_recommendations_isolated_by_application(): void
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();
        $app1 = Application::factory()->for($tenant1)->create();
        $app2 = Application::factory()->for($tenant2)->create();

        $this->createMetric($app1, 1, 100, now()->subDays(10)->toDateString(), 20);
        $this->createMetric($app1, 1, 100, now()->subDays(2)->toDateString(), 5);
        $this->createMetric($app2, 2, 200, now()->subDays(10)->toDateString(), 20);
        $this->createMetric($app2, 2, 200, now()->subDays(2)->toDateString(), 5);

        $recommendations = $this->advisor->detectSalesDrops($app1->id);

        $this->assertCount(1, $recommendations);
        $this->assertEquals(1, $recommendations->first()['seller_id']);
    }
}
