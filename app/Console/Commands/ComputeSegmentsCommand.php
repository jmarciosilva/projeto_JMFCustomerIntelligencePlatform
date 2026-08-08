<?php

namespace App\Console\Commands;

use App\Actions\SegmentContactsAction;
use Illuminate\Console\Command;

class ComputeSegmentsCommand extends Command
{
    protected $signature = 'intelligence:compute-segments {--tenant-id=}';

    protected $description = 'Compute customer scores and segments for all contacts';

    public function handle(SegmentContactsAction $action): int
    {
        $tenantId = $this->option('tenant-id');

        $this->info('Computing customer segments...');

        $updated = $action->execute($tenantId ? (int) $tenantId : null);

        $this->info("✅ Updated $updated contacts with customer scores and segments");

        return self::SUCCESS;
    }
}
