<?php

namespace App\Domain\Affiliate\Actions;

use App\Domain\Affiliate\Enums\StatusSprintA;
use App\Models\AffiliateProduct;
use App\Models\ProductOpportunity;
use App\Models\Trend;

class CreateProductOpportunityAction
{
    public function execute(
        int $applicationId,
        Trend $trend,
        AffiliateProduct $affiliateProduct,
        int $discoveryOpportunityScore,
        array $opportunityScoreBreakdown,
        int $purchaseIntentScore,
        string $purchaseIntentLabel,
        array $purchaseIntentBreakdown,
    ): ProductOpportunity {
        return ProductOpportunity::create([
            'application_id' => $applicationId,
            'trend_id' => $trend->id,
            'affiliate_product_id' => $affiliateProduct->id,
            'status_sprint_a' => StatusSprintA::DISCOVERED,
            'discovery_opportunity_score' => $discoveryOpportunityScore,
            'opportunity_score_breakdown' => $opportunityScoreBreakdown,
            'purchase_intent_score' => $purchaseIntentScore,
            'purchase_intent_label' => $purchaseIntentLabel,
            'purchase_intent_breakdown' => $purchaseIntentBreakdown,
            'expires_at' => now()->addDays(7),
        ]);
    }
}
