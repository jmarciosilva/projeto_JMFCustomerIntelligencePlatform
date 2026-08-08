<?php

namespace App\Actions;

use App\Domain\Intelligence\CustomerScoreCalculator;
use App\Domain\Intelligence\SegmentationEngine;
use App\Models\Contact;
use App\Models\CustomerSegment;

class SegmentContactsAction
{
    public function __construct(
        private CustomerScoreCalculator $scoreCalculator,
        private SegmentationEngine $segmentationEngine,
    ) {}

    public function execute(?int $tenantId = null): int
    {
        $query = Contact::query();

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $contacts = $query->get();
        $updated = 0;

        foreach ($contacts as $contact) {
            $scores = $this->scoreCalculator->calculate($contact);
            $segment = $this->segmentationEngine->segment($contact, $scores);

            // Update contact with segment
            $contact->update([
                'segment' => $segment,
                'customer_score' => $scores['customer_score'],
                'customer_score_computed_at' => now(),
            ]);

            // Create or update segment record
            CustomerSegment::updateOrCreate(
                ['contact_id' => $contact->id, 'segment' => $segment],
                [
                    'customer_score' => $scores['customer_score'],
                    'recency_score' => $scores['recency_score'],
                    'frequency_score' => $scores['frequency_score'],
                    'monetary_score' => $scores['monetary_score'],
                    'segmented_at' => now(),
                ]
            );

            $updated++;
        }

        return $updated;
    }
}
