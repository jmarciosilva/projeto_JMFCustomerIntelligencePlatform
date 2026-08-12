<?php

namespace App\Domain\Affiliate;

/**
 * Classifica intenção de compra de uma keyword ou frase.
 * Thresholds: LOW (0-39), MEDIUM (40-69), HIGH (70-100)
 * Sprint A MVP: Determinístico, sem ML.
 */
class PurchaseIntentClassifier
{
    public function classify(string $keyword): array
    {
        $keyword_lower = strtolower($keyword);

        // PASSO 1: Detectar base intent (preferir mais forte)
        $base_intent = $this->detectBaseIntent($keyword_lower);

        // PASSO 2: Score base por intent
        $score = match ($base_intent) {
            'INFORMATIONAL' => 15,              // Baixo mas não zero
            'INVESTIGATION' => PurchaseIntentTerms::INVESTIGATION_BASE,  // 45
            'TRANSACTIONAL' => PurchaseIntentTerms::TRANSACTIONAL_BASE,  // 75
        };

        // PASSO 3: Aplicar ajustes

        // Se houver termo informacional, penalizar
        if ($this->matchesTerms($keyword_lower, PurchaseIntentTerms::INFORMATIONAL)) {
            $score += PurchaseIntentTerms::INFORMATIONAL_PENALTY; // -10
        }

        // Se houver múltiplos sinais transacionais, bônus
        $transactional_count = $this->countMatches($keyword_lower, PurchaseIntentTerms::TRANSACTIONAL);
        if ($transactional_count >= 2) {
            $score += PurchaseIntentTerms::MULTIPLE_SIGNALS_BONUS; // +15
        }

        // Se houver intensidade e houver algum sinal significativo, bônus
        if ($this->matchesTerms($keyword_lower, PurchaseIntentTerms::INTENSITY_MODIFIERS)
            && $base_intent !== 'INFORMATIONAL') {
            $score += PurchaseIntentTerms::INTENSITY_BONUS; // +5
        }

        // PASSO 4: Limitar ao intervalo 0-100
        $score = max(0, min(100, $score));

        // PASSO 5: Determinar label
        $label = match (true) {
            $score < 40 => 'LOW',
            $score < 70 => 'MEDIUM',
            default => 'HIGH'
        };

        return [
            'score' => $score,
            'label' => $label,
            'breakdown' => [
                'base_intent' => $base_intent,
                'base_score' => $this->baseScoreForIntent($base_intent),
                'matched_signals' => $this->getMatchedSignals($keyword_lower),
                'adjustments' => [
                    'informational_penalty' => $this->matchesTerms($keyword_lower, PurchaseIntentTerms::INFORMATIONAL)
                        ? PurchaseIntentTerms::INFORMATIONAL_PENALTY : 0,
                    'multiple_signals_bonus' => ($transactional_count >= 2)
                        ? PurchaseIntentTerms::MULTIPLE_SIGNALS_BONUS : 0,
                    'intensity_bonus' => ($this->matchesTerms($keyword_lower, PurchaseIntentTerms::INTENSITY_MODIFIERS)
                        && $base_intent !== 'INFORMATIONAL')
                        ? PurchaseIntentTerms::INTENSITY_BONUS : 0,
                ],
                'final_score' => $score,
            ],
        ];
    }

    private function detectBaseIntent(string $keyword): string
    {
        // Preferir intent mais forte
        if ($this->matchesTerms($keyword, PurchaseIntentTerms::TRANSACTIONAL)) {
            return 'TRANSACTIONAL';
        }

        if ($this->matchesTerms($keyword, PurchaseIntentTerms::INVESTIGATION)) {
            return 'INVESTIGATION';
        }

        return 'INFORMATIONAL';
    }

    private function matchesTerms(string $keyword, array $terms): bool
    {
        foreach ($terms as $term) {
            if (str_contains($keyword, $term)) {
                return true;
            }
        }

        return false;
    }

    private function countMatches(string $keyword, array $terms): int
    {
        $count = 0;
        foreach ($terms as $term) {
            if (str_contains($keyword, $term)) {
                $count++;
            }
        }

        return $count;
    }

    private function baseScoreForIntent(string $intent): int
    {
        return match ($intent) {
            'INFORMATIONAL' => 15,
            'INVESTIGATION' => PurchaseIntentTerms::INVESTIGATION_BASE,
            'TRANSACTIONAL' => PurchaseIntentTerms::TRANSACTIONAL_BASE,
        };
    }

    private function getMatchedSignals(string $keyword): array
    {
        $matched = [];

        $categories = [
            'INFORMATIONAL' => PurchaseIntentTerms::INFORMATIONAL,
            'INVESTIGATION' => PurchaseIntentTerms::INVESTIGATION,
            'TRANSACTIONAL' => PurchaseIntentTerms::TRANSACTIONAL,
            'INTENSITY_MODIFIERS' => PurchaseIntentTerms::INTENSITY_MODIFIERS,
        ];

        foreach ($categories as $category => $terms) {
            foreach ($terms as $term) {
                if (str_contains($keyword, $term)) {
                    $matched[$category][] = $term;
                }
            }
        }

        return array_filter($matched);
    }
}
