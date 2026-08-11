<?php

namespace App\Domain\Affiliate\Actions;

use App\Domain\Affiliate\Enums\StatusSprintA;
use App\Models\CurationDecision;
use App\Models\ProductOpportunity;
use App\Models\User;

class RejectProductOpportunityAction
{
    public function execute(
        ProductOpportunity $opportunity,
        User $rejecter,
        string $reason,
    ): ProductOpportunity {
        $opportunity->update([
            'status_sprint_a' => StatusSprintA::REJECTED,
        ]);

        CurationDecision::create([
            'application_id' => $opportunity->application_id,
            'product_opportunity_id' => $opportunity->id,
            'user_id' => $rejecter->id,
            'decision' => CurationDecision::DECISION_REJECTED,
            'reason' => $reason,
            'decided_at' => now(),
        ]);

        return $opportunity->refresh();
    }
}
