<?php

namespace App\Console\Commands;

use App\Jobs\CollectTrendSignalsJob;
use App\Models\Trend;
use App\Models\Watchlist;
use Illuminate\Console\Command;

class CollectTrendsCommand extends Command
{
    protected $signature = 'trends:collect {--application-id=}';

    protected $description = 'Despacha a coleta de sinais de tendência para todas as tendências ativas de watchlists ativas';

    public function handle(): int
    {
        $applicationId = $this->option('application-id');
        $applicationId = $applicationId ? (int) $applicationId : null;

        $trendIds = Trend::query()
            ->where('status', Trend::STATUS_ACTIVE)
            ->whereHas('watchlist', function ($query) use ($applicationId): void {
                $query->where('status', Watchlist::STATUS_ACTIVE);

                if ($applicationId) {
                    $query->where('application_id', $applicationId);
                }
            })
            ->pluck('id');

        foreach ($trendIds as $trendId) {
            CollectTrendSignalsJob::dispatch($trendId);
        }

        $this->info("✅ Coleta despachada para {$trendIds->count()} tendência(s).");

        return self::SUCCESS;
    }
}
