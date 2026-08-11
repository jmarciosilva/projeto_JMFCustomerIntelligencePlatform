<?php

namespace App\Domain\Affiliate\Actions;

use App\Domain\Affiliate\Enums\StatusSprintA;
use App\Models\ProductOpportunity;

class PublishProductOpportunityAction
{
    public function execute(ProductOpportunity $opportunity): ProductOpportunity
    {
        if ($opportunity->status_sprint_a !== StatusSprintA::APPROVED) {
            throw new \InvalidArgumentException(
                'Apenas oportunidades aprovadas podem ser publicadas. Status atual: ' . $opportunity->status_sprint_a->value
            );
        }

        $opportunity->update([
            'status_sprint_a' => StatusSprintA::PUBLISHED,
            'published_at' => now(),
        ]);

        return $opportunity->refresh();
    }
}
