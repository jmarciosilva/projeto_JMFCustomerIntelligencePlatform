<?php

namespace App\Jobs;

use App\Actions\MatchTrendProductsAction;
use App\Models\Trend;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class MatchTrendProductsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(private Trend $trend) {}

    public function handle(MatchTrendProductsAction $action): void
    {
        $action->execute($this->trend);
    }
}
