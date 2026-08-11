<?php

namespace App\Domain\Affiliate\Actions;

use App\Domain\Affiliate\Enums\StatusSprintA;
use App\Models\CurationDecision;
use App\Models\ProductOpportunity;
use App\Models\User;

class ApproveProductOpportunityAction
{
    public function execute(
        ProductOpportunity $opportunity,
        User $approver,
        ?string $reason = null,
    ): ProductOpportunity {
        $opportunity->update([
            'status_sprint_a' => StatusSprintA::APPROVED,
            'approved_at' => now(),
        ]);

        CurationDecision::create([
            'application_id' => $opportunity->application_id,
            'product_opportunity_id' => $opportunity->id,
            'user_id' => $approver->id,
            'decision' => CurationDecision::DECISION_APPROVED,
            'reason' => $reason,
            'decided_at' => now(),
        ]);

        return $opportunity->refresh();
    }
}
