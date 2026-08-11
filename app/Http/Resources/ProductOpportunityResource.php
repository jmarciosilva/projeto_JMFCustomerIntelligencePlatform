<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductOpportunityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'application_id' => $this->application_id,
            'status' => $this->status_sprint_a?->value,
            'trend_id' => $this->trend_id,
            'affiliate_product_id' => $this->affiliate_product_id,
            'discovery_opportunity_score' => $this->discovery_opportunity_score,
            'opportunity_score_breakdown' => $this->opportunity_score_breakdown,
            'purchase_intent' => [
                'score' => $this->purchase_intent_score,
                'label' => $this->purchase_intent_label?->value,
                'breakdown' => $this->purchase_intent_breakdown,
            ],
            'performance_score' => [
                'actual' => $this->actual_performance_score,
                'breakdown' => $this->performance_score_breakdown,
            ],
            'lifecycle' => [
                'approved_at' => $this->approved_at?->toIso8601String(),
                'published_at' => $this->published_at?->toIso8601String(),
                'expired_at' => $this->expired_at?->toIso8601String(),
                'expires_at' => $this->expires_at?->toIso8601String(),
                'expiration_reason' => $this->expiration_reason,
            ],
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
