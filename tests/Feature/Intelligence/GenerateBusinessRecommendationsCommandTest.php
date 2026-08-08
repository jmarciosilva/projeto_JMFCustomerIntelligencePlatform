<?php

namespace Tests\Feature\Intelligence;

use App\Models\Application;
use App\Models\MarketplaceMetric;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateBusinessRecommendationsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_executes_successfully(): void
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

        $this->artisan('intelligence:generate-recommendations')
            ->assertExitCode(0);

        $this->assertDatabaseHas('business_recommendations', ['application_id' => $app->id]);
    }

    public function test_command_with_application_id_filter(): void
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

        $this->artisan('intelligence:generate-recommendations', ['--application-id' => $app1->id])
            ->assertExitCode(0);

        $this->assertDatabaseHas('business_recommendations', ['application_id' => $app1->id]);
        $this->assertDatabaseMissing('business_recommendations', ['application_id' => $app2->id]);
    }
}
