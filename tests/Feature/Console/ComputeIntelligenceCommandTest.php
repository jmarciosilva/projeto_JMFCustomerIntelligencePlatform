<?php

use App\Models\Application;
use App\Models\Contact;
use App\Models\Event;
use App\Models\ProductAffinity;
use App\Models\Visitor;

test('roda o cálculo de lead score e afinidade, de forma idempotente', function () {
    $application = Application::factory()->create();
    $contact = Contact::factory()->for($application->tenant)->create();
    $visitor = Visitor::factory()->for($application)->create(['visitor_id' => 'v1', 'contact_id' => $contact->id]);

    Event::factory()->for($application)->create([
        'visitor_id' => $visitor->visitor_id,
        'event_name' => 'contact.form_submitted',
    ]);

    Event::factory()->for($application)->create(['visitor_id' => 'v2', 'subject_type' => 'Product', 'subject_id' => '1']);
    Event::factory()->for($application)->create(['visitor_id' => 'v2', 'subject_type' => 'Product', 'subject_id' => '2']);

    $this->artisan('intelligence:compute')->assertSuccessful();

    expect($contact->fresh()->lead_score)->toBe(20);
    expect(ProductAffinity::query()->where('application_id', $application->id)->count())->toBe(1);

    $this->artisan('intelligence:compute')->assertSuccessful();

    expect(ProductAffinity::query()->where('application_id', $application->id)->count())->toBe(1);
});
