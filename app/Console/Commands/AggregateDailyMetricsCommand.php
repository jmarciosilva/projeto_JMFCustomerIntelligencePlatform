<?php

namespace App\Console\Commands;

use App\Models\Application;
use App\Models\DailyMetric;
use App\Models\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class AggregateDailyMetricsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metrics:aggregate-daily {--date= : Data a agregar (Y-m-d). Padrão: ontem.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Agrega métricas diárias de eventos (daily_metrics) por aplicação';

    public function handle(): int
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))->startOfDay()
            : now()->subDay()->startOfDay();

        $from = $date->clone()->startOfDay();
        $to = $date->clone()->endOfDay();

        $applications = Application::all();

        foreach ($applications as $application) {
            $this->aggregateFor($application, $date, $from, $to);
        }

        $this->info("Métricas agregadas para {$date->toDateString()} ({$applications->count()} aplicações).");

        return self::SUCCESS;
    }

    private function aggregateFor(Application $application, Carbon $date, Carbon $from, Carbon $to): void
    {
        $baseQuery = fn () => Event::query()
            ->where('application_id', $application->id)
            ->whereBetween('occurred_at', [$from, $to]);

        $metrics = [
            'events_total' => $baseQuery()->count(),
            'visitors_unique' => $baseQuery()->distinct('visitor_id')->count('visitor_id'),
            'sessions_unique' => $baseQuery()->whereNotNull('session_id')->distinct('session_id')->count('session_id'),
            'pageviews_total' => $baseQuery()->where('event_name', 'page.viewed')->count(),
        ];

        if ($application->conversion_event_name) {
            $metrics['conversions_total'] = $baseQuery()->where('event_name', $application->conversion_event_name)->count();
        }

        foreach ($metrics as $key => $value) {
            // updateOrCreate() compara o valor bruto de `date` na busca, sem
            // passar pelo cast do model — evita usá-lo aqui, pois o formato
            // persistido pelo cast `date` pode não bater byte a byte com a
            // string de busca, gerando duplicidade em vez de atualização.
            $metric = DailyMetric::query()
                ->where('application_id', $application->id)
                ->where('metric_key', $key)
                ->whereDate('date', $date->toDateString())
                ->first();

            if ($metric) {
                $metric->update(['metric_value' => $value]);
            } else {
                DailyMetric::query()->create([
                    'application_id' => $application->id,
                    'tenant_id' => $application->tenant_id,
                    'date' => $date->toDateString(),
                    'metric_key' => $key,
                    'metric_value' => $value,
                ]);
            }
        }
    }
}
