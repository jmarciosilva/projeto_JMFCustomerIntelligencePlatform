<?php

namespace Tests\Feature\Intelligence;

use App\Actions\ComputeCustomerScoresAction;
use App\Domain\Intelligence\CustomerScoreCalculator;
use App\Models\Contact;
use App\Models\Event;
use App\Models\Tenant;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerScoreTest extends TestCase
{
    use RefreshDatabase;

    private CustomerScoreCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new CustomerScoreCalculator;
    }

    public function test_customer_score_calculated_correctly_for_active_buyer(): void
    {
        $tenant = Tenant::factory()->create();
        $contact = Contact::factory()->create([
            'tenant_id' => $tenant->id,
            'last_seen_at' => now()->subDays(5),
        ]);

        $visitor = Visitor::factory()->create(['contact_id' => $contact->id]);

        // Create purchase events
        Event::factory()->count(3)->create([
            'visitor_id' => $visitor->id,
            'contact_id' => $contact->id,
            'event_name' => 'purchase.completed',
            'properties' => ['total_value' => 100],
            'occurred_at' => now()->subDays(2),
        ]);

        $scores = $this->calculator->calculate($contact);

        $this->assertGreaterThan(0, $scores['customer_score']);
        $this->assertGreaterThan(0, $scores['recency_score']);
        $this->assertGreaterThan(0, $scores['frequency_score']);
        $this->assertGreaterThan(0, $scores['monetary_score']);
    }

    public function test_customer_score_zero_for_inactive_contact(): void
    {
        $tenant = Tenant::factory()->create();
        $contact = Contact::factory()->create(['tenant_id' => $tenant->id]);

        $scores = $this->calculator->calculate($contact);

        $this->assertEquals(0, $scores['customer_score']);
    }

    public function test_recency_score_high_for_recent_activity(): void
    {
        $tenant = Tenant::factory()->create();
        $contact = Contact::factory()->create([
            'tenant_id' => $tenant->id,
            'last_seen_at' => now()->subDays(3),
        ]);

        $visitor = Visitor::factory()->create(['contact_id' => $contact->id]);
        Event::factory()->create([
            'visitor_id' => $visitor->id,
            'contact_id' => $contact->id,
            'event_name' => 'product.viewed',
        ]);

        $scores = $this->calculator->calculate($contact);

        $this->assertEquals(100, $scores['recency_score']);
    }

    public function test_frequency_score_increases_with_purchases(): void
    {
        $tenant = Tenant::factory()->create();
        $contact = Contact::factory()->create([
            'tenant_id' => $tenant->id,
            'last_seen_at' => now(),
        ]);

        $visitor = Visitor::factory()->create(['contact_id' => $contact->id]);

        // 5 purchases should give 80 score
        Event::factory()->count(5)->create([
            'visitor_id' => $visitor->id,
            'contact_id' => $contact->id,
            'event_name' => 'purchase.completed',
        ]);

        $scores = $this->calculator->calculate($contact);

        $this->assertEquals(80, $scores['frequency_score']);
    }

    public function test_monetary_score_based_on_total_value(): void
    {
        $tenant = Tenant::factory()->create();
        $contact = Contact::factory()->create([
            'tenant_id' => $tenant->id,
            'last_seen_at' => now(),
        ]);

        $visitor = Visitor::factory()->create(['contact_id' => $contact->id]);

        Event::factory()->create([
            'visitor_id' => $visitor->id,
            'contact_id' => $contact->id,
            'event_name' => 'purchase.completed',
            'properties' => ['total_value' => 600],
        ]);

        $scores = $this->calculator->calculate($contact);

        $this->assertEquals(80, $scores['monetary_score']);
    }

    public function test_compute_customer_scores_action(): void
    {
        $tenant = Tenant::factory()->create();
        Contact::factory()->count(5)->create(['tenant_id' => $tenant->id]);

        $action = new ComputeCustomerScoresAction($this->calculator);
        $updated = $action->execute($tenant->id);

        $this->assertEquals(5, $updated);
        $this->assertTrue(Contact::where('tenant_id', $tenant->id)
            ->whereNotNull('customer_score_computed_at')
            ->exists());
    }

    public function test_customer_score_max_bounded_at_100(): void
    {
        $tenant = Tenant::factory()->create();
        $contact = Contact::factory()->create([
            'tenant_id' => $tenant->id,
            'last_seen_at' => now()->subDays(2),
        ]);

        $visitor = Visitor::factory()->create(['contact_id' => $contact->id]);

        Event::factory()->count(15)->create([
            'visitor_id' => $visitor->id,
            'contact_id' => $contact->id,
            'event_name' => 'purchase.completed',
            'properties' => ['total_value' => 5000],
        ]);

        $scores = $this->calculator->calculate($contact);

        $this->assertLessThanOrEqual(100, $scores['customer_score']);
    }
}
