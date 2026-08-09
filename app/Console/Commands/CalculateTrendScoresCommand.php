<?php

namespace App\Console\Commands;

use App\Application\Trends\Actions\CalculateTrendScoresAction;
use Illuminate\Console\Command;

class CalculateTrendScoresCommand extends Command
{
    protected $signature = 'trends:calculate-scores {--application-id=}';

    protected $description = 'Recalcula o Trend Score (0-100) de todas as tendências ativas';

    public function handle(CalculateTrendScoresAction $action): int
    {
        $applicationId = $this->option('application-id');
        $applicationId = $applicationId ? (int) $applicationId : null;

        $updated = $action->handle($applicationId);

        $this->info("✅ Trend Score recalculado para {$updated} tendência(s).");

        return self::SUCCESS;
    }
}
