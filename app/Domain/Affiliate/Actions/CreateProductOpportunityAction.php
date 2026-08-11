<?php

namespace App\Domain\Affiliate\Actions;

use App\Domain\Affiliate\Enums\StatusSprintA;
use App\Domain\Affiliate\PerformanceScoreCalculator;
use App\Domain\Affiliate\RecurrencyCalculator;
use App\Models\AffiliateProduct;
use App\Models\ProductOpportunity;
use App\Models\Trend;

class CreateProductOpportunityAction
{
    public function __construct(
        protected RecurrencyCalculator $recurrencyCalculator,
        protected PerformanceScoreCalculator $performanceCalculator,
    ) {}

    public function execute(
        int $applicationId,
        Trend $trend,
        AffiliateProduct $affiliateProduct,
        int $discoveryOpportunityScore,
        array $opportunityScoreBreakdown,
        int $purchaseIntentScore,
        string $purchaseIntentLabel,
        array $purchaseIntentBreakdown,
        ?int $contactId = null,
    ): ProductOpportunity {
        // Sprint B2: Calcular recurrency_rate baseado no histórico de conversões
        $recurrencyRate = null;
        if ($contactId !== null) {
            $recurrencyRate = $this->recurrencyCalculator->calculateForContact(
                contactId: $contactId,
                applicationId: $applicationId
            );
        }

        // Determinar confidence level baseado em fatores disponíveis
        // Para agora, assumimos que sempre temos CTR + ConversionRate dados discovery phase
        // Recurrency é terceiro fator
        $confidenceLevel = $recurrencyRate !== null ? 'HIGH' : 'MEDIUM';

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
            'recurrency_rate' => $recurrencyRate,
            'confidence_level' => $confidenceLevel,
            'expires_at' => now()->addDays(7),
        ]);
    }
}
