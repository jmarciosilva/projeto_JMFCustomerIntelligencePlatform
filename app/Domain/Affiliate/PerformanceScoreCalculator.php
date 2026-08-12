<?php

namespace App\Domain\Affiliate;

/**
 * Calcula Performance Score baseado em dados reais de cliques, conversões e recorrência.
 * Sprint A MVP: CTR + ConversionRate. Recurrency = null
 * Sprint B: Adiciona Recurrency factor (conversões / 90 dias)
 *
 * Pesos redistribuídos quando fatores estão indisponíveis:
 * - Com 3 fatores: CTR 40%, ConversionRate 40%, Recurrency 20%
 * - Com 2 fatores: redistribui pesos proporcionalmente
 * - Com um fator: recebe 100% do peso disponível
 * - Sem fatores: score = null, confidence = INSUFFICIENT_DATA
 *
 * Confidence Levels:
 * - INSUFFICIENT_DATA: 0 fatores
 * - LOW: 1 fator (até 40% de pesos)
 * - MEDIUM: 2 fatores (40-80% de pesos)
 * - HIGH: 3+ fatores (80%+ de pesos)
 */
class PerformanceScoreCalculator
{
    // Pesos originais (para referência futura Sprint B)
    private const WEIGHT_CTR = 40;              // 40%

    private const WEIGHT_CONVERSION_RATE = 40;  // 40%

    private const WEIGHT_RECURRENCY = 20;       // 20% (Sprint B)

    /**
     * Calcula performance score com base em dados disponíveis.
     * Sprint A: clicks, conversions, impressions (recurrency = null)
     * Sprint B: adiciona recurrency_rate (conversões / 90 dias, normalizado 0-100)
     */
    public function calculate(
        ?int $clicks,
        ?int $conversions,
        ?int $impressions,
        ?float $recurrency_rate = null
    ): array {
        $factors = [];
        $available_weights = [];

        // PASSO 1: Avaliar disponibilidade de cada fator

        // Fator 1: CTR
        if ($impressions !== null && $impressions > 0 && $clicks !== null) {
            $ctr_value = ($clicks / $impressions) * 100;
            $factors['ctr'] = min(100, $ctr_value);
            $available_weights['ctr'] = self::WEIGHT_CTR;
        } else {
            $factors['ctr'] = null;
            $available_weights['ctr'] = 0; // Fator indisponível
        }

        // Fator 2: Conversion Rate
        if ($clicks !== null && $clicks > 0 && $conversions !== null) {
            $conv_rate_value = ($conversions / $clicks) * 100;
            $factors['conversion_rate'] = min(100, $conv_rate_value);
            $available_weights['conversion_rate'] = self::WEIGHT_CONVERSION_RATE;
        } else {
            $factors['conversion_rate'] = null;
            $available_weights['conversion_rate'] = 0;
        }

        // Fator 3: Recurrency (conversões / 90 dias, normalizado 0-100)
        // Sprint B: Agora recurrence_rate é um parâmetro
        if ($recurrency_rate !== null && $recurrency_rate >= 0) {
            $factors['recurrency'] = min(100, $recurrency_rate);
            $available_weights['recurrency'] = self::WEIGHT_RECURRENCY;
        } else {
            $factors['recurrency'] = null;
            $available_weights['recurrency'] = 0;  // Não participar da normalização
        }

        // PASSO 2: Calcular soma de pesos disponíveis
        $total_available_weight = array_sum($available_weights);

        // PASSO 3: Se nenhum fator disponível, retornar null
        if ($total_available_weight === 0) {
            return [
                'score' => null,
                'confidence' => 'INSUFFICIENT_DATA',
                'factors' => $factors,
                'breakdown' => [
                    'ctr' => null,
                    'conversion_rate' => null,
                    'recurrency' => null,
                    'reason' => 'No factors available',
                ],
            ];
        }

        // PASSO 4: Normalizar pesos apenas dos fatores disponíveis
        $normalized_weights = [];
        foreach ($available_weights as $factor => $weight) {
            if ($weight > 0) {
                $normalized_weights[$factor] = ($weight / $total_available_weight) * 100;
            }
        }

        // PASSO 5: Calcular score ponderado
        $score = 0;

        if ($factors['ctr'] !== null) {
            $score += ($factors['ctr'] * $normalized_weights['ctr'] / 100);
        }

        if ($factors['conversion_rate'] !== null) {
            $score += ($factors['conversion_rate'] * $normalized_weights['conversion_rate'] / 100);
        }

        if ($factors['recurrency'] !== null) {
            $score += ($factors['recurrency'] * $normalized_weights['recurrency'] / 100);
        }

        // PASSO 6: Determinar confiança baseado em dados disponíveis
        $confidence = $this->getConfidenceLevel($available_weights);

        return [
            'score' => (int) $score,
            'confidence' => $confidence,
            'factors' => $factors,
            'breakdown' => [
                'ctr' => $factors['ctr'],
                'ctr_normalized_weight' => $normalized_weights['ctr'] ?? null,
                'conversion_rate' => $factors['conversion_rate'],
                'conversion_rate_normalized_weight' => $normalized_weights['conversion_rate'] ?? null,
                'recurrency' => $factors['recurrency'],
                'recurrency_normalized_weight' => $normalized_weights['recurrency'] ?? null,
                'calculation' => $this->explainCalculation($factors, $normalized_weights),
            ],
        ];
    }

    private function getConfidenceLevel(array $available_weights): string
    {
        $total = array_sum($available_weights);

        if ($total === 0) {
            return 'INSUFFICIENT_DATA';
        }

        // 3 de 3 fatores: HIGH (Sprint B)
        if ($total >= 100) {
            return 'HIGH';
        }

        // 2 de 3 ou mais: MEDIUM (Sprint A com ambos fatores)
        if ($total >= 60) {
            return 'MEDIUM';
        }

        // 1 de 3: LOW (Sprint A com um fator)
        return 'LOW';
    }

    private function explainCalculation(array $factors, array $weights): string
    {
        $parts = [];

        if ($factors['ctr'] !== null && isset($weights['ctr'])) {
            $parts[] = sprintf('CTR: %.1f × %.1f%% = %.1f',
                $factors['ctr'], $weights['ctr'],
                $factors['ctr'] * $weights['ctr'] / 100);
        }

        if ($factors['conversion_rate'] !== null && isset($weights['conversion_rate'])) {
            $parts[] = sprintf('Conv: %.1f × %.1f%% = %.1f',
                $factors['conversion_rate'], $weights['conversion_rate'],
                $factors['conversion_rate'] * $weights['conversion_rate'] / 100);
        }

        if ($factors['recurrency'] !== null && isset($weights['recurrency'])) {
            $parts[] = sprintf('Rec: %.1f × %.1f%% = %.1f',
                $factors['recurrency'], $weights['recurrency'],
                $factors['recurrency'] * $weights['recurrency'] / 100);
        }

        return implode(' + ', $parts);
    }
}
