<?php

namespace App\Domain\Affiliate;

use App\Models\AffiliateConversion;
use Carbon\Carbon;

/**
 * Calcula taxa de recorrência baseada no histórico real de conversões.
 * Sprint B2: Conversões / 90 dias, normalizado 0-100.
 *
 * Fórmula:
 * recurrency_rate = (conversões nos últimos 90 dias / 90) × 100, capped at 100
 *
 * Exemplo:
 * - 8 conversões em 90 dias → (8/90)×100 = 8.89%
 * - 90+ conversões em 90 dias → 100% (capped)
 * - 0 conversões em 90 dias → 0% ou null (sem dados históricos)
 */
class RecurrencyCalculator
{
    /**
     * Calcula recurrency_rate baseado no histórico de conversões de um contato.
     *
     * @param  int  $contactId  ID do contato/lead
     * @param  int  $applicationId  ID da aplicação (multi-tenant)
     * @param  Carbon|null  $referenceDate  Data de referência (default: agora)
     * @return float|null Taxa de recorrência 0-100, ou null se sem dados
     */
    public function calculateForContact(
        int $contactId,
        int $applicationId,
        ?Carbon $referenceDate = null
    ): ?float {
        $referenceDate ??= now();
        $startDate = $referenceDate->copy()->subDays(90);

        // Contar conversões do contato nos últimos 90 dias
        $conversionCount = AffiliateConversion::query()
            ->where('application_id', $applicationId)
            ->where('contact_id', $contactId)
            ->whereBetween('created_at', [$startDate, $referenceDate])
            ->count();

        // Sem conversões = null (não confundir com 0)
        if ($conversionCount === 0) {
            return null;
        }

        return $this->normalizeRecurrency($conversionCount);
    }

    /**
     * Calcula recurrency_rate baseado em count direto de conversões.
     * Útil para testes e cálculos de batch.
     *
     * @param  int  $conversionCount  Número de conversões no período (90 dias)
     * @return float Taxa de recorrência 0-100
     */
    public function normalizeRecurrency(int $conversionCount): float
    {
        if ($conversionCount <= 0) {
            return 0.0;
        }

        // Fórmula: (conversões / 90) × 100
        $rate = ($conversionCount / 90) * 100;

        // Cap at 100
        return min(100.0, $rate);
    }

    /**
     * Calcula recurrency para um período customizado.
     * Útil para análise retroativa ou períodos diferentes.
     *
     * @param  int  $conversionCount  Número de conversões
     * @param  int  $days  Período em dias (ex: 90, 180, 365)
     * @return float Taxa normalizada 0-100
     */
    public function normalizeForPeriod(int $conversionCount, int $days): float
    {
        if ($days <= 0 || $conversionCount <= 0) {
            return 0.0;
        }

        $rate = ($conversionCount / $days) * 100;

        return min(100.0, $rate);
    }
}
