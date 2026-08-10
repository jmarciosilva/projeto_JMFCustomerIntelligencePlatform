<?php

namespace App\Console\Commands;

use App\Application\Trends\Actions\CalculateTrendScoresAction;
use App\Application\Trends\Actions\MatchTrendProductsAction;
use Illuminate\Console\Command;

class CalculateTrendScoresCommand extends Command
{
    protected $signature = 'trends:calculate-scores {--application-id=}';

    protected $description = 'Recalcula o Trend Score (0-100) e relaciona com produtos de afiliados';

    public function handle(CalculateTrendScoresAction $calculateAction, MatchTrendProductsAction $matchAction): int
    {
        $applicationId = $this->option('application-id');
        $applicationId = $applicationId ? (int) $applicationId : null;

        $updated = $calculateAction->handle($applicationId);
        $this->info("✅ Trend Score recalculado para {$updated} tendência(s).");

        $matched = $matchAction->handle($applicationId);
        $this->info("✅ {$matched} produto(s) relacionado(s) a tendências.");

        return self::SUCCESS;
    }
}
