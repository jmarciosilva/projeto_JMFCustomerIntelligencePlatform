<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Tenant;
use App\Models\Trend;
use Illuminate\Database\Seeder;

class Phase24TrendScoreSeeder extends Seeder
{
    /**
     * Calcula e popula Trend Scores (0-100) para Phase 24
     * Usa regras: mentions, engagement, velocity, recência
     */
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'jmf-system')->firstOrFail();
        $application = Application::where('tenant_id', $tenant->id)
            ->where('slug', 'magazine-voce-afiliados')
            ->firstOrFail();

        $trends = Trend::where('application_id', $application->id)->get();

        foreach ($trends as $trend) {
            if (! $trend->trend_score_breakdown) {
                continue;
            }

            $breakdown = $trend->trend_score_breakdown;
            $mentions = $breakdown['mentions'] ?? 0;
            $engagement = $breakdown['engagement'] ?? 0;
            $velocity = $breakdown['velocity'] ?? 0;

            // Cálculo do score (0-100) baseado em regras
            $score = $this->calculateTrendScore(
                mentions: $mentions,
                engagement: $engagement,
                velocity: $velocity,
                lastCollected: $trend->last_collected_at
            );

            $trend->update([
                'trend_score' => $score,
                'trend_score_computed_at' => now(),
            ]);
        }

        $this->command->info("Phase 24 - Trend Score: {$trends->count()} trends rescoreados (0-100).");
    }

    /**
     * Calcula score de 0-100 usando múltiplos fatores
     */
    private function calculateTrendScore(
        int $mentions,
        int $engagement,
        float $velocity,
        ?\DateTimeInterface $lastCollected
    ): float {
        $mentionScore = min(($mentions / 100) * 10, 35);
        $engagementScore = min(($engagement / 50) * 10, 30);
        $velocityScore = min(($velocity / 5) * 10, 25);

        $recencyScore = 10;
        if ($lastCollected) {
            $hoursSince = now()->diffInHours($lastCollected);
            if ($hoursSince <= 24) {
                $recencyScore = 10;
            } elseif ($hoursSince <= 72) {
                $recencyScore = 7;
            } elseif ($hoursSince <= 168) {
                $recencyScore = 4;
            } else {
                $recencyScore = 0;
            }
        }

        $totalScore = $mentionScore + $engagementScore + $velocityScore + $recencyScore;

        return round(min($totalScore, 100), 2);
    }
}
