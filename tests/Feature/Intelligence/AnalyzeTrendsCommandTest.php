<?php

namespace Tests\Feature\Intelligence;

use App\Models\Application;
use App\Models\MarketplaceMetric;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyzeTrendsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_executes_successfully(): void
    {
        $tenant = Tenant::factory()->create();
        $app = Application::factory()->for($tenant)->create();

        MarketplaceMetric::create([
            'tenant_id' => $tenant->id,
            'application_id' => $app->id,
            'seller_id' => 1,
            'product_id' => 1,
            'date' => now()->subDays(1)->toDateString(),
            'product_views' => 10,
            'revenue' => 100,
            'purchases' => 2,
        ]);

        $this->artisan('intelligence:analyze-trends')
            ->assertExitCode(0);

        $this->assertDatabaseHas('product_trends', ['application_id' => $app->id, 'product_id' => 1]);
        $this->assertDatabaseHas('sales_forecasts', ['application_id' => $app->id]);
    }

    public function test_command_with_application_id_filter(): void
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();
        $app1 = Application::factory()->for($tenant1)->create();
        $app2 = Application::factory()->for($tenant2)->create();

        MarketplaceMetric::create([
            'tenant_id' => $tenant1->id, 'application_id' => $app1->id, 'seller_id' => 1,
            'product_id' => 1, 'date' => now()->subDays(1)->toDateString(), 'product_views' => 10,
        ]);
        MarketplaceMetric::create([
            'tenant_id' => $tenant2->id, 'application_id' => $app2->id, 'seller_id' => 1,
            'product_id' => 2, 'date' => now()->subDays(1)->toDateString(), 'product_views' => 10,
        ]);

        $this->artisan('intelligence:analyze-trends', ['--application-id' => $app1->id])
            ->assertExitCode(0);

        $this->assertDatabaseHas('product_trends', ['application_id' => $app1->id]);
        $this->assertDatabaseMissing('product_trends', ['application_id' => $app2->id]);
    }
}
