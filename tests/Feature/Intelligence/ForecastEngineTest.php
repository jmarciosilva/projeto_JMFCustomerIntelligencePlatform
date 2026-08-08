<?php

namespace Tests\Feature\Intelligence;

use App\Actions\ForecastSalesAction;
use App\Domain\Intelligence\ForecastEngine;
use App\Models\Application;
use App\Models\MarketplaceMetric;
use App\Models\SalesForecast;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForecastEngineTest extends TestCase
{
    use RefreshDatabase;

    private ForecastEngine $engine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->engine = new ForecastEngine;
    }

    private function createMetric(Application $app, string $date, float $revenue, int $purchases, ?int $sellerId = null): MarketplaceMetric
    {
        return MarketplaceMetric::create([
            'tenant_id' => $app->tenant_id,
            'application_id' => $app->id,
            'seller_id' => $sellerId,
            'product_id' => 1,
            'date' => $date,
            'revenue' => $revenue,
            'purchases' => $purchases,
        ]);
    }

    public function test_forecast_returns_zero_with_no_historical_data(): void
    {
        $tenant = Tenant::factory()->create();
        $app = Application::factory()->for($tenant)->create();

        $forecast = $this->engine->forecast($app->id, 7);

        $this->assertEquals(0.0, $forecast['predicted_revenue']);
        $this->assertEquals(SalesForecast::CONFIDENCE_LOW, $forecast['confidence']);
    }

    public function test_forecast_predicts_based_on_average_revenue(): void
    {
        $tenant = Tenant::factory()->create();
        $app = Application::factory()->for($tenant)->create();

        // 10 days of consistent revenue at 100/day
        for ($i = 1; $i <= 10; $i++) {
            $this->createMetric($app, now()->subDays($i)->toDateString(), 100, 5);
        }

        $forecast = $this->engine->forecast($app->id, 7);

        // 7 days * ~100/day = ~700, with trend factor near 1.0
        $this->assertGreaterThan(500, $forecast['predicted_revenue']);
        $this->assertLessThan(900, $forecast['predicted_revenue']);
    }

    public function test_forecast_confidence_increases_with_more_data(): void
    {
        $tenant = Tenant::factory()->create();
        $app = Application::factory()->for($tenant)->create();

        for ($i = 1; $i <= 25; $i++) {
            $this->createMetric($app, now()->subDays($i)->toDateString(), 50, 2);
        }

        $forecast = $this->engine->forecast($app->id, 7);

        $this->assertEquals(SalesForecast::CONFIDENCE_HIGH, $forecast['confidence']);
    }

    public function test_forecast_never_returns_negative_values(): void
    {
        $tenant = Tenant::factory()->create();
        $app = Application::factory()->for($tenant)->create();

        $this->createMetric($app, now()->subDays(1)->toDateString(), 10, 1);

        $forecast = $this->engine->forecast($app->id, 30);

        $this->assertGreaterThanOrEqual(0, $forecast['predicted_revenue']);
        $this->assertGreaterThanOrEqual(0, $forecast['predicted_purchases']);
    }

    public function test_forecast_isolated_by_seller(): void
    {
        $tenant = Tenant::factory()->create();
        $app = Application::factory()->for($tenant)->create();

        $this->createMetric($app, now()->subDays(1)->toDateString(), 1000, 10, 1);
        $this->createMetric($app, now()->subDays(1)->toDateString(), 10, 1, 2);

        $forecastSeller1 = $this->engine->forecast($app->id, 7, 1);
        $forecastSeller2 = $this->engine->forecast($app->id, 7, 2);

        $this->assertGreaterThan($forecastSeller2['predicted_revenue'], $forecastSeller1['predicted_revenue']);
    }

    public function test_forecast_sales_action_persists_records(): void
    {
        $tenant = Tenant::factory()->create();
        $app = Application::factory()->for($tenant)->create();

        $this->createMetric($app, now()->subDays(1)->toDateString(), 100, 5);

        $action = new ForecastSalesAction($this->engine);
        $created = $action->execute($app->id);

        $this->assertGreaterThan(0, $created);
        $this->assertDatabaseHas('sales_forecasts', [
            'application_id' => $app->id,
            'seller_id' => null,
            'horizon_days' => 7,
        ]);
        $this->assertDatabaseHas('sales_forecasts', [
            'application_id' => $app->id,
            'seller_id' => null,
            'horizon_days' => 30,
        ]);
    }
}
