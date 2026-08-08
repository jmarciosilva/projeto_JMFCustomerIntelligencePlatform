<?php

namespace Tests\Feature\Intelligence;

use App\Domain\Intelligence\OpportunityDetector;
use App\Models\Application;
use App\Models\Contact;
use App\Models\Event;
use App\Models\ProductAffinity;
use App\Models\Tenant;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpportunityDetectorTest extends TestCase
{
    use RefreshDatabase;

    private OpportunityDetector $detector;

    protected function setUp(): void
    {
        parent::setUp();
        $this->detector = new OpportunityDetector;
    }

    public function test_cross_sell_detected_for_moderate_affinity(): void
    {
        $app = Application::factory()->create();

        ProductAffinity::factory()->create([
            'application_id' => $app->id,
            'subject_id_a' => '10',
            'subject_id_b' => '20',
            'co_occurrences' => 5,
        ]);

        $opportunities = $this->detector->detectCrossSell($app->id);

        $this->assertCount(1, $opportunities);
        $this->assertEquals('10', $opportunities->first()['product_id']);
        $this->assertEquals('20', $opportunities->first()['related_product_id']);
    }

    public function test_cross_sell_excludes_low_co_occurrence_pairs(): void
    {
        $app = Application::factory()->create();

        ProductAffinity::factory()->create([
            'application_id' => $app->id,
            'co_occurrences' => 1,
        ]);

        $opportunities = $this->detector->detectCrossSell($app->id);

        $this->assertCount(0, $opportunities);
    }

    public function test_cross_sell_excludes_strong_affinity_reserved_for_bundles(): void
    {
        $app = Application::factory()->create();

        ProductAffinity::factory()->create([
            'application_id' => $app->id,
            'co_occurrences' => 10,
        ]);

        $crossSell = $this->detector->detectCrossSell($app->id);
        $bundles = $this->detector->detectBundles($app->id);

        $this->assertCount(0, $crossSell);
        $this->assertCount(1, $bundles);
    }

    public function test_up_sell_identifies_high_score_converted_contacts(): void
    {
        $tenant = Tenant::factory()->create();
        $app = Application::factory()->for($tenant)->create();

        $contact = Contact::factory()->create([
            'tenant_id' => $tenant->id,
            'segment' => 'converted',
            'customer_score' => 75,
        ]);

        $visitor = Visitor::factory()->create(['contact_id' => $contact->id]);
        Event::factory()->create([
            'visitor_id' => $visitor->id,
            'contact_id' => $contact->id,
            'event_name' => 'purchase.completed',
            'properties' => ['total_value' => 100],
        ]);

        $opportunities = $this->detector->detectUpSell($app->id);

        $this->assertCount(1, $opportunities);
        $this->assertEquals($contact->id, $opportunities->first()['contact_id']);
    }

    public function test_up_sell_excludes_low_score_contacts(): void
    {
        $tenant = Tenant::factory()->create();
        $app = Application::factory()->for($tenant)->create();

        Contact::factory()->create([
            'tenant_id' => $tenant->id,
            'segment' => 'converted',
            'customer_score' => 30,
        ]);

        $opportunities = $this->detector->detectUpSell($app->id);

        $this->assertCount(0, $opportunities);
    }

    public function test_win_back_identifies_inactive_contacts_with_purchase_history(): void
    {
        $tenant = Tenant::factory()->create();
        $app = Application::factory()->for($tenant)->create();

        $contact = Contact::factory()->create([
            'tenant_id' => $tenant->id,
            'segment' => 'inactive',
            'last_seen_at' => now()->subDays(45),
        ]);

        $visitor = Visitor::factory()->create(['contact_id' => $contact->id]);
        Event::factory()->create([
            'visitor_id' => $visitor->id,
            'contact_id' => $contact->id,
            'event_name' => 'purchase.completed',
            'properties' => ['total_value' => 250],
        ]);

        $opportunities = $this->detector->detectWinBack($app->id);

        $this->assertCount(1, $opportunities);
        $this->assertEquals(250.0, $opportunities->first()['potential_value']);
    }

    public function test_win_back_excludes_inactive_contacts_without_purchases(): void
    {
        $tenant = Tenant::factory()->create();
        $app = Application::factory()->for($tenant)->create();

        Contact::factory()->create([
            'tenant_id' => $tenant->id,
            'segment' => 'inactive',
            'last_seen_at' => now()->subDays(45),
        ]);

        $opportunities = $this->detector->detectWinBack($app->id);

        $this->assertCount(0, $opportunities);
    }

    public function test_opportunities_isolated_by_application(): void
    {
        $app1 = Application::factory()->create();
        $app2 = Application::factory()->create();

        ProductAffinity::factory()->create(['application_id' => $app1->id, 'co_occurrences' => 5]);
        ProductAffinity::factory()->create(['application_id' => $app2->id, 'co_occurrences' => 5]);

        $opportunities = $this->detector->detectCrossSell($app1->id);

        $this->assertCount(1, $opportunities);
    }
}
