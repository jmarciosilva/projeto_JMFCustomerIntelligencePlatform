<?php

namespace Tests\Feature\Intelligence;

use App\Actions\SegmentContactsAction;
use App\Domain\Intelligence\CustomerScoreCalculator;
use App\Domain\Intelligence\SegmentationEngine;
use App\Models\Contact;
use App\Models\Event;
use App\Models\Tenant;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SegmentationTest extends TestCase
{
    use RefreshDatabase;

    private SegmentationEngine $engine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->engine = new SegmentationEngine;
    }

    public function test_vip_segment_identified_correctly(): void
    {
        $tenant = Tenant::factory()->create();
        $contact = Contact::factory()->create([
            'tenant_id' => $tenant->id,
            'last_seen_at' => now()->subDays(5),
        ]);

        $visitor = Visitor::factory()->create(['contact_id' => $contact->id]);

        // Create 2 purchases (recurrent) with high value
        Event::factory()->count(2)->create([
            'visitor_id' => $visitor->id,
            'contact_id' => $contact->id,
            'event_name' => 'purchase.completed',
            'properties' => ['total_value' => 500],
        ]);

        $scores = ['customer_score' => 85, 'recency_score' => 100, 'frequency_score' => 100, 'monetary_score' => 80];
        $segment = $this->engine->segment($contact, $scores);

        $this->assertEquals(SegmentationEngine::SEGMENT_VIP, $segment);
    }

    public function test_new_segment_identified_correctly(): void
    {
        $tenant = Tenant::factory()->create();
        $contact = Contact::factory()->create([
            'tenant_id' => $tenant->id,
            'first_identified_at' => now()->subDays(3),
            'last_seen_at' => now(),
        ]);

        $scores = ['customer_score' => 30, 'recency_score' => 100, 'frequency_score' => 20, 'monetary_score' => 0];
        $segment = $this->engine->segment($contact, $scores);

        $this->assertEquals(SegmentationEngine::SEGMENT_NEW, $segment);
    }

    public function test_inactive_segment_identified_correctly(): void
    {
        $tenant = Tenant::factory()->create();
        $contact = Contact::factory()->create([
            'tenant_id' => $tenant->id,
            'last_seen_at' => now()->subDays(35),
        ]);

        $scores = ['customer_score' => 20, 'recency_score' => 0, 'frequency_score' => 40, 'monetary_score' => 20];
        $segment = $this->engine->segment($contact, $scores);

        $this->assertEquals(SegmentationEngine::SEGMENT_INACTIVE, $segment);
    }

    public function test_converted_segment_identified_correctly(): void
    {
        $tenant = Tenant::factory()->create();
        $contact = Contact::factory()->create([
            'tenant_id' => $tenant->id,
            'first_identified_at' => now()->subDays(30),
            'last_seen_at' => now()->subDays(10),
        ]);

        $visitor = Visitor::factory()->create(['contact_id' => $contact->id]);
        Event::factory()->create([
            'visitor_id' => $visitor->id,
            'contact_id' => $contact->id,
            'event_name' => 'purchase.completed',
            'properties' => ['total_value' => 100],
        ]);

        $scores = ['customer_score' => 40, 'recency_score' => 60, 'frequency_score' => 40, 'monetary_score' => 40];
        $segment = $this->engine->segment($contact, $scores);

        $this->assertEquals(SegmentationEngine::SEGMENT_CONVERTED, $segment);
    }

    public function test_engaged_segment_identified_correctly(): void
    {
        $tenant = Tenant::factory()->create();
        $contact = Contact::factory()->create([
            'tenant_id' => $tenant->id,
            'first_identified_at' => now()->subDays(20),
            'last_seen_at' => now()->subDays(2),
        ]);

        $visitor = Visitor::factory()->create(['contact_id' => $contact->id]);
        Event::factory()->count(3)->create([
            'visitor_id' => $visitor->id,
            'contact_id' => $contact->id,
            'event_name' => 'product.viewed',
        ]);

        $scores = ['customer_score' => 60, 'recency_score' => 80, 'frequency_score' => 40, 'monetary_score' => 20];
        $segment = $this->engine->segment($contact, $scores);

        $this->assertEquals(SegmentationEngine::SEGMENT_ENGAGED, $segment);
    }

    public function test_segment_contacts_action(): void
    {
        $tenant = Tenant::factory()->create();
        Contact::factory()->count(10)->create(['tenant_id' => $tenant->id]);

        $calculator = new CustomerScoreCalculator;
        $action = new SegmentContactsAction($calculator, $this->engine);
        $updated = $action->execute($tenant->id);

        $this->assertEquals(10, $updated);
        $this->assertTrue(Contact::where('tenant_id', $tenant->id)
            ->whereNotNull('segment')
            ->count() > 0);
    }

    public function test_segment_record_created(): void
    {
        $tenant = Tenant::factory()->create();
        $contact = Contact::factory()->create(['tenant_id' => $tenant->id]);

        $calculator = new CustomerScoreCalculator;
        $action = new SegmentContactsAction($calculator, $this->engine);
        $action->execute($tenant->id);

        $this->assertTrue($contact->segments()->exists());
    }
}
