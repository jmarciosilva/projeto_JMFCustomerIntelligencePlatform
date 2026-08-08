<?php

namespace App\Console\Commands;

use App\Actions\GenerateBusinessRecommendationsAction;
use Illuminate\Console\Command;

class GenerateBusinessRecommendationsCommand extends Command
{
    protected $signature = 'intelligence:generate-recommendations {--application-id=}';

    protected $description = 'Generate actionable business recommendations for marketplace sellers';

    public function handle(GenerateBusinessRecommendationsAction $action): int
    {
        $applicationId = $this->option('application-id');
        $applicationId = $applicationId ? (int) $applicationId : null;

        $this->info('Generating business recommendations...');

        $generated = $action->execute($applicationId);

        $this->info("✅ Generated $generated business recommendations");

        return self::SUCCESS;
    }
}
