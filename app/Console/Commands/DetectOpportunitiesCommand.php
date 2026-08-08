<?php

namespace App\Console\Commands;

use App\Actions\DetectOpportunitiesAction;
use Illuminate\Console\Command;

class DetectOpportunitiesCommand extends Command
{
    protected $signature = 'intelligence:detect-opportunities {--application-id=}';

    protected $description = 'Detect commercial opportunities (cross-sell, up-sell, win-back, bundles)';

    public function handle(DetectOpportunitiesAction $action): int
    {
        $applicationId = $this->option('application-id');
        $applicationId = $applicationId ? (int) $applicationId : null;

        $this->info('Detecting commercial opportunities...');

        $detected = $action->execute($applicationId);

        $this->info("✅ Detected $detected opportunities");

        return self::SUCCESS;
    }
}
