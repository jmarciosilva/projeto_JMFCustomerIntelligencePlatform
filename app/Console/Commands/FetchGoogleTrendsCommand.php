<?php

namespace App\Console\Commands;

use App\Domain\Trends\FetchGoogleTrendsAction;
use Illuminate\Console\Command;

class FetchGoogleTrendsCommand extends Command
{
    protected $signature = 'trends:fetch-google-trends
                            {--region=BR : Região (BR, US, GLOBAL)}';

    protected $description = 'Busca trending topics do Google Trends e salva no banco';

    public function handle()
    {
        $region = $this->option('region');
        $this->info("📊 Buscando trending topics do Google Trends (região: {$region})...");

        $action = new FetchGoogleTrendsAction($region);
        $trends = $action->execute();

        $this->info('✅ '.count($trends).' trending topics capturados!');

        foreach ($trends as $trend) {
            $growth = $trend['growth_percentage'] ?? 'N/A';
            $this->line("  • {$trend['topic']} ({$growth}% ↑)");
        }

        return Command::SUCCESS;
    }
}
