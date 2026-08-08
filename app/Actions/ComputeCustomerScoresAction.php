<?php

namespace App\Actions;

use App\Domain\Intelligence\CustomerScoreCalculator;
use App\Models\Contact;
use Illuminate\Support\Facades\DB;

class ComputeCustomerScoresAction
{
    public function __construct(private CustomerScoreCalculator $calculator) {}

    public function execute(?int $tenantId = null): int
    {
        $query = Contact::query();

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $contacts = $query->get();
        $updated = 0;

        foreach ($contacts as $contact) {
            $scores = $this->calculator->calculate($contact);

            $contact->update([
                'customer_score' => $scores['customer_score'],
                'customer_score_computed_at' => now(),
            ]);

            $updated++;
        }

        return $updated;
    }
}
