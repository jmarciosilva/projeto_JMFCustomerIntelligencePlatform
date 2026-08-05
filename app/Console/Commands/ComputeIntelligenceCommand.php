<?php

namespace App\Console\Commands;

use App\Application\Intelligence\Actions\ComputeLeadScoresAction;
use App\Application\Intelligence\Actions\ComputeProductAffinitiesAction;
use App\Models\Application;
use Illuminate\Console\Command;

class ComputeIntelligenceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'intelligence:compute';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalcula lead score dos contatos e afinidade entre produtos por aplicação';

    public function handle(ComputeLeadScoresAction $leadScores, ComputeProductAffinitiesAction $affinities): int
    {
        $leadScores->handle();
        $this->info('Lead scores recalculados.');

        $applications = Application::all();

        foreach ($applications as $application) {
            $affinities->handle($application);
        }

        $this->info("Afinidade entre produtos recalculada para {$applications->count()} aplicações.");

        return self::SUCCESS;
    }
}
