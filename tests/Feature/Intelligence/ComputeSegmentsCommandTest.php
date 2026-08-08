<?php

namespace Tests\Feature\Intelligence;

use App\Models\Contact;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComputeSegmentsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_compute_segments_command_executes_successfully(): void
    {
        $tenant = Tenant::factory()->create();
        Contact::factory()->count(5)->create(['tenant_id' => $tenant->id]);

        $this->artisan('intelligence:compute-segments')
            ->assertExitCode(0);

        $this->assertTrue(Contact::whereNotNull('customer_score_computed_at')->exists());
    }

    public function test_compute_segments_command_with_tenant_id(): void
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        Contact::factory()->count(5)->create(['tenant_id' => $tenant1->id]);
        Contact::factory()->count(3)->create(['tenant_id' => $tenant2->id]);

        $this->artisan('intelligence:compute-segments', [
            '--tenant-id' => $tenant1->id,
        ])->assertExitCode(0);

        $this->assertEquals(5, Contact::where('tenant_id', $tenant1->id)
            ->whereNotNull('customer_score_computed_at')
            ->count());
    }

    public function test_compute_segments_updates_segment_field(): void
    {
        $tenant = Tenant::factory()->create();
        Contact::factory()->create(['tenant_id' => $tenant->id]);

        $this->artisan('intelligence:compute-segments')->assertExitCode(0);

        $this->assertTrue(Contact::where('tenant_id', $tenant->id)
            ->whereNotNull('segment')
            ->exists());
    }
}
