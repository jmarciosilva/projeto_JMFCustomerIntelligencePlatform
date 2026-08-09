<?php

namespace App\Jobs;

use App\Application\Trends\Actions\CollectTrendSignalsAction;
use App\Models\Trend;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class CollectTrendSignalsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public readonly int $trendId) {}

    public function handle(CollectTrendSignalsAction $action): void
    {
        $trend = Trend::find($this->trendId);

        if (! $trend) {
            return;
        }

        $action->handle($trend);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Falha ao coletar sinais de tendência.', [
            'trend_id' => $this->trendId,
            'exception' => $exception->getMessage(),
        ]);
    }
}
