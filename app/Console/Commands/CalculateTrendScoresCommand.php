<?php

namespace App\Console\Commands;

use App\Application\Affiliate\Actions\CalculateOpportunitiesAction;
use App\Application\Trends\Actions\CalculateTrendScoresAction;
use App\Application\Trends\Actions\MatchTrendProductsAction;
use Illuminate\Console\Command;

class CalculateTrendScoresCommand extends Command
{
    protected $signature = 'trends:calculate-scores {--application-id=}';

    protected $description = 'Recalcula Trend Score, Product Matcher e Opportunity Score';

    public function handle(
        CalculateTrendScoresAction $calculateAction,
        MatchTrendProductsAction $matchAction,
        CalculateOpportunitiesAction $opportunityAction
    ): int {
        $applicationId = $this->option('application-id');
        $applicationId = $applicationId ? (int) $applicationId : null;

        $updated = $calculateAction->handle($applicationId);
        $this->info("✅ Trend Score recalculado para {$updated} tendência(s).");

        $matched = $matchAction->handle($applicationId);
        $this->info("✅ {$matched} produto(s) relacionado(s) a tendências.");

        $opportunities = $opportunityAction->handle($applicationId);
        $this->info("✅ {$opportunities} oportunidade(s) calculada(s).");

        return self::SUCCESS;
    }
}
