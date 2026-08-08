<?php

namespace App\Console\Commands;

use App\Actions\AnalyzeTrendsAction;
use App\Actions\ForecastSalesAction;
use Illuminate\Console\Command;

class AnalyzeTrendsCommand extends Command
{
    protected $signature = 'intelligence:analyze-trends {--application-id=}';

    protected $description = 'Analyze product trends and forecast sales for all applications';

    public function handle(AnalyzeTrendsAction $trendsAction, ForecastSalesAction $forecastAction): int
    {
        $applicationId = $this->option('application-id');
        $applicationId = $applicationId ? (int) $applicationId : null;

        $this->info('Analyzing product trends...');
        $trendsUpdated = $trendsAction->execute($applicationId);
        $this->info("✅ Updated $trendsUpdated product trends");

        $this->info('Forecasting sales...');
        $forecastsCreated = $forecastAction->execute($applicationId);
        $this->info("✅ Created $forecastsCreated sales forecasts");

        return self::SUCCESS;
    }
}
