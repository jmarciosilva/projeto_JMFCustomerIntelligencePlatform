<?php

namespace Tests\Feature\Intelligence;

use App\Models\Application;
use App\Models\ProductAffinity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DetectOpportunitiesCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_executes_successfully(): void
    {
        $app = Application::factory()->create();
        ProductAffinity::factory()->create(['application_id' => $app->id, 'co_occurrences' => 5]);

        $this->artisan('intelligence:detect-opportunities')
            ->assertExitCode(0);

        $this->assertDatabaseHas('opportunities', ['application_id' => $app->id]);
    }

    public function test_command_with_application_id_filter(): void
    {
        $app1 = Application::factory()->create();
        $app2 = Application::factory()->create();

        ProductAffinity::factory()->create(['application_id' => $app1->id, 'co_occurrences' => 5]);
        ProductAffinity::factory()->create(['application_id' => $app2->id, 'co_occurrences' => 5]);

        $this->artisan('intelligence:detect-opportunities', ['--application-id' => $app1->id])
            ->assertExitCode(0);

        $this->assertDatabaseHas('opportunities', ['application_id' => $app1->id]);
        $this->assertDatabaseMissing('opportunities', ['application_id' => $app2->id]);
    }
}
