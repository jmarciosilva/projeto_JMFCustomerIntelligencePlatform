<?php

namespace App\Http\Controllers\Api;

use App\Application\Analytics\Actions\GetDashboardOverviewAction;
use App\Domain\Analytics\DateRange;
use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MetricsController extends Controller
{
    public function __construct(private readonly GetDashboardOverviewAction $action) {}

    /**
     * Métricas agregadas consumidas pelo componente Livewire Dashboard do
     * SDK cliente. Reaproveita a mesma Action usada pelo painel admin
     * interno (Analytics), remapeando as chaves de "totals" (events_total,
     * visitors_unique, ...) para o formato plano (events, visitors, ...)
     * que o SDK espera.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $application = $request->user('sanctum');

        abort_unless($application instanceof Application, 401);

        $range = $this->resolveRange($request);

        $result = $this->action->handle($application, $range);

        return response()->json([
            'events' => $result['totals']['events_total'],
            'visitors' => $result['totals']['visitors_unique'],
            'sessions' => $result['totals']['sessions_unique'],
            'conversions' => $result['totals']['conversions_total'] ?? 0,
            'trend' => collect($result['trend'])
                ->mapWithKeys(fn (array $day) => [
                    Carbon::parse($day['date'])->format('d/m') => $day['events_total'],
                ])
                ->all(),
        ]);
    }

    private function resolveRange(Request $request): DateRange
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        if ($startDate && $endDate) {
            return new DateRange(
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay(),
            );
        }

        return DateRange::lastDays(7);
    }
}
