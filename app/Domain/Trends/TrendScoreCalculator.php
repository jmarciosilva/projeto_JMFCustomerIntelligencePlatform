<?php

namespace App\Domain\Trends;

use App\Models\Trend;
use App\Models\TrendSnapshot;
use Illuminate\Database\Eloquent\Collection;

/**
 * Trend Score (0-100), baseado em regras — mede apenas nível de interesse/
 * tendência, nunca intenção comercial (isso é o Product Opportunity Engine,
 * Fase 26). Fatores considerados: crescimento recente, volume de
 * ocorrências, recorrência e estabilidade — todos calculados sobre a janela
 * de snapshots mais recentes de um `Trend`. Engajamento entra quando
 * disponível (nem toda fonte fornece esse dado, ex. `InternalBehaviorProvider`
 * nunca preenche `engagement`); quando ausente, seu peso é redistribuído
 * proporcionalmente entre os demais fatores, em vez de penalizar a
 * tendência por um dado que nenhum provider ativo fornece.
 *
 * Sazonalidade (citada no `PROMPT_INICIAL...`/`ROADMAP.md` como fator
 * possível) foi deliberadamente deixada de fora da fórmula nesta primeira
 * versão: exigiria pelo menos um ano de histórico comparável (mesmo período
 * do ano anterior), que a plataforma ainda não tem para nenhum Trend real.
 * A arquitetura (fatores nomeados + pesos) permite adicioná-la depois sem
 * quebrar o restante do cálculo.
 */
class TrendScoreCalculator
{
    private const WINDOW_SIZE = 5;

    private const VOLUME_CEILING = 50;

    private const ENGAGEMENT_CEILING = 200;

    /**
     * @var array<string, float>
     */
    private const WEIGHTS = [
        'growth' => 0.30,
        'volume' => 0.25,
        'recurrence' => 0.20,
        'stability' => 0.15,
        'engagement' => 0.10,
    ];

    /**
     * @return array{score: float, breakdown: array<string, mixed>}|null null quando não há snapshots suficientes.
     */
    public function calculate(Trend $trend): ?array
    {
        $snapshots = $trend->snapshots()
            ->orderByDesc('collected_at')
            ->limit(self::WINDOW_SIZE)
            ->get();

        if ($snapshots->isEmpty()) {
            return null;
        }

        $factors = array_filter([
            'growth' => $this->growthFactor($snapshots),
            'volume' => $this->volumeFactor($snapshots),
            'recurrence' => $this->recurrenceFactor($snapshots),
            'stability' => $this->stabilityFactor($snapshots),
            'engagement' => $this->engagementFactor($snapshots),
        ], fn (?float $value) => $value !== null);

        if ($factors === []) {
            return null;
        }

        $totalWeight = array_sum(array_map(fn (string $key) => self::WEIGHTS[$key], array_keys($factors)));
        $weightedSum = array_sum(array_map(
            fn (string $key, float $value) => $value * self::WEIGHTS[$key],
            array_keys($factors),
            $factors,
        ));

        $score = round(min(100, max(0, $weightedSum / $totalWeight)), 2);

        return [
            'score' => $score,
            'breakdown' => [
                'factors' => $factors,
                'window_size' => self::WINDOW_SIZE,
                'snapshots_considered' => $snapshots->count(),
            ],
        ];
    }

    /**
     * Crescimento recente: velocidade (%) do snapshot mais atual, mapeada
     * para 0-100 (0% de crescimento = 50 pontos, neutro).
     *
     * @param  Collection<int, TrendSnapshot>  $snapshots
     */
    private function growthFactor(Collection $snapshots): ?float
    {
        $velocity = $snapshots->first()->velocity;

        if ($velocity === null) {
            return null;
        }

        return $this->clamp(50 + ((float) $velocity / 2));
    }

    /**
     * Volume de ocorrências: média de menções na janela, normalizada contra
     * um teto configurável.
     *
     * @param  Collection<int, TrendSnapshot>  $snapshots
     */
    private function volumeFactor(Collection $snapshots): ?float
    {
        $mentions = $snapshots->pluck('mentions')->filter(fn ($value) => $value !== null);

        if ($mentions->isEmpty()) {
            return null;
        }

        return $this->clamp(($mentions->avg() / self::VOLUME_CEILING) * 100);
    }

    /**
     * Recorrência: fração dos snapshots da janela com menções > 0 — uma
     * tendência que aparece de forma consistente, não um pico isolado.
     *
     * @param  Collection<int, TrendSnapshot>  $snapshots
     */
    private function recurrenceFactor(Collection $snapshots): ?float
    {
        $withMentions = $snapshots->filter(fn (TrendSnapshot $snapshot) => $snapshot->mentions !== null);

        if ($withMentions->isEmpty()) {
            return null;
        }

        $positive = $withMentions->filter(fn (TrendSnapshot $snapshot) => $snapshot->mentions > 0)->count();

        return ($positive / $withMentions->count()) * 100;
    }

    /**
     * Estabilidade: fração dos snapshots da janela sem queda relevante
     * (velocidade >= -10%) — penaliza tendências que sobem e caem de forma
     * errática, sem exigir crescimento constante.
     *
     * @param  Collection<int, TrendSnapshot>  $snapshots
     */
    private function stabilityFactor(Collection $snapshots): ?float
    {
        $withVelocity = $snapshots->pluck('velocity')->filter(fn ($value) => $value !== null);

        if ($withVelocity->isEmpty()) {
            return null;
        }

        $nonCollapsing = $withVelocity->filter(fn ($value) => (float) $value >= -10.0)->count();

        return ($nonCollapsing / $withVelocity->count()) * 100;
    }

    /**
     * Engajamento: média na janela, normalizada contra um teto configurável.
     * Ausente sempre que nenhum provider ativo o preenche (ex.:
     * `InternalBehaviorProvider`) — nesse caso o fator é excluído do cálculo.
     *
     * @param  Collection<int, TrendSnapshot>  $snapshots
     */
    private function engagementFactor(Collection $snapshots): ?float
    {
        $engagement = $snapshots->pluck('engagement')->filter(fn ($value) => $value !== null);

        if ($engagement->isEmpty()) {
            return null;
        }

        return $this->clamp(($engagement->avg() / self::ENGAGEMENT_CEILING) * 100);
    }

    private function clamp(float $value): float
    {
        return min(100.0, max(0.0, $value));
    }
}
