<?php

namespace App\Domain\Affiliate\Actions;

use App\Domain\Affiliate\PerformanceScoreCalculator;
use App\Domain\Affiliate\RecurrencyCalculator;
use App\Models\ProductOpportunity;

/**
 * Recalcula o performance score de uma ProductOpportunity baseado em conversões reais.
 * Sprint B3: Atualizar scores quando conversões são importadas/atualizadas.
 *
 * Fluxo:
 * 1. Contar cliques, conversões, impressões reais
 * 2. Recalcular recurrency_rate do contato
 * 3. Calcular novo performance score
 * 4. Atualizar opportunity com breakdown
 */
class UpdatePerformanceScoreAction
{
    public function __construct(
        protected PerformanceScoreCalculator $performanceCalculator,
        protected RecurrencyCalculator $recurrencyCalculator,
    ) {}

    public function execute(ProductOpportunity $opportunity): ProductOpportunity
    {
        // PASSO 1: Coletar dados de performance real (apenas conversões e recurrency)
        $conversions = $opportunity->affiliateConversions()
            ->where('status', 'approved')
            ->count();

        // PASSO 2: Recalcular recurrency_rate
        // Tentar obter contact_id de uma conversão da opportunity
        $contactId = $opportunity->affiliateConversions()
            ->where('status', 'approved')
            ->whereNotNull('contact_id')
            ->value('contact_id');

        $recurrencyRate = null;
        if ($contactId !== null) {
            $recurrencyRate = $this->recurrencyCalculator->calculateForContact(
                contactId: $contactId,
                applicationId: $opportunity->application_id
            );
        }

        // PASSO 3: Calcular novo performance score
        // Com conversões reais + recurrency (sem clicks/impressions para agora)
        $result = $this->performanceCalculator->calculate(
            clicks: null,
            conversions: $conversions > 0 ? $conversions : null,
            impressions: null,
            recurrency_rate: $recurrencyRate
        );

        // PASSO 4: Atualizar opportunity com novos dados
        $opportunity->update([
            'actual_performance_score' => $result['score'],
            'performance_score_breakdown' => $result['breakdown'],
            'confidence_level' => $result['confidence'],
        ]);

        return $opportunity->fresh();
    }
}
