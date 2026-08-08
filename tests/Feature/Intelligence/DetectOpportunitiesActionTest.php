<?php

namespace Tests\Feature\Intelligence;

use App\Actions\DetectOpportunitiesAction;
use App\Domain\Intelligence\OpportunityDetector;
use App\Models\Application;
use App\Models\Opportunity;
use App\Models\ProductAffinity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DetectOpportunitiesActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_action_persists_detected_opportunities(): void
    {
        $app = Application::factory()->create();

        ProductAffinity::factory()->create([
            'application_id' => $app->id,
            'co_occurrences' => 5,
        ]);

        $action = new DetectOpportunitiesAction(new OpportunityDetector);
        $detected = $action->execute($app->id);

        $this->assertGreaterThan(0, $detected);
        $this->assertDatabaseHas('opportunities', [
            'application_id' => $app->id,
            'type' => Opportunity::TYPE_CROSS_SELL,
        ]);
    }

    public function test_action_clears_stale_opportunities_before_recomputing(): void
    {
        $app = Application::factory()->create();

        Opportunity::create([
            'application_id' => $app->id,
            'type' => Opportunity::TYPE_CROSS_SELL,
            'product_id' => 999,
            'related_product_id' => 998,
            'score' => 50,
            'reason' => 'stale opportunity',
            'detected_at' => now()->subDays(5),
        ]);

        $action = new DetectOpportunitiesAction(new OpportunityDetector);
        $action->execute($app->id);

        $this->assertDatabaseMissing('opportunities', ['product_id' => 999]);
    }

    public function test_action_isolated_by_application_filter(): void
    {
        $app1 = Application::factory()->create();
        $app2 = Application::factory()->create();

        ProductAffinity::factory()->create(['application_id' => $app1->id, 'co_occurrences' => 5]);
        ProductAffinity::factory()->create(['application_id' => $app2->id, 'co_occurrences' => 5]);

        $action = new DetectOpportunitiesAction(new OpportunityDetector);
        $action->execute($app1->id);

        $this->assertDatabaseHas('opportunities', ['application_id' => $app1->id]);
        $this->assertDatabaseMissing('opportunities', ['application_id' => $app2->id]);
    }
}
