<?php

namespace Tests\Feature\Console;

use App\Jobs\ValidateAffiliateProductDataJob;
use App\Models\AffiliateProduct;
use App\Models\Application;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class ValidateAffiliateProductDataCommandTest extends TestCase
{
    public function test_command_dispatches_validation_jobs(): void
    {
        Bus::fake();

        $app = Application::factory()->create();
        AffiliateProduct::factory()->count(2)->create(['application_id' => $app->id]);

        $this->artisan('affiliate:validate-product-data', ['--application' => $app->id])
            ->assertSuccessful();

        Bus::assertDispatchedTimes(ValidateAffiliateProductDataJob::class, 2);
    }

    public function test_command_filters_by_application(): void
    {
        Bus::fake();

        $app1 = Application::factory()->create();
        $app2 = Application::factory()->create();

        AffiliateProduct::factory()->create(['application_id' => $app1->id]);
        AffiliateProduct::factory()->create(['application_id' => $app2->id]);

        $this->artisan('affiliate:validate-product-data', ['--application' => $app1->id])
            ->assertSuccessful();

        Bus::assertDispatchedTimes(ValidateAffiliateProductDataJob::class, 1);
    }

    public function test_command_handles_no_products(): void
    {
        Bus::fake();

        $app = Application::factory()->create();

        $this->artisan('affiliate:validate-product-data', ['--application' => $app->id])
            ->assertSuccessful();

        Bus::assertNotDispatched(ValidateAffiliateProductDataJob::class);
    }
}
