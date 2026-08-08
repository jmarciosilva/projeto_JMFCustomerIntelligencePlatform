<?php

namespace App\Livewire\Admin\Intelligence;

use App\Models\Application;
use App\Models\Contact;
use App\Models\Opportunity;
use App\Models\ProductTrend;
use App\Models\SalesForecast;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class BusinessIntelligenceDashboard extends Component
{
    public ?int $applicationId = null;

    public function mount(): void
    {
        $this->applicationId = Application::query()->where('is_active', true)->orderBy('name')->value('id')
            ?? Application::query()->orderBy('name')->value('id');
    }

    public function render(): View
    {
        $applications = Application::query()->orderBy('name')->get();
        $application = $applications->firstWhere('id', $this->applicationId);

        if (! $application) {
            return view('livewire.admin.intelligence.business-intelligence-dashboard', [
                'applications' => $applications,
                'application' => null,
            ]);
        }

        return view('livewire.admin.intelligence.business-intelligence-dashboard', [
            'applications' => $applications,
            'application' => $application,
            'segments' => $this->getSegmentBreakdown($application),
            'risingProducts' => $this->getProductTrends($application, ProductTrend::DIRECTION_RISING),
            'fallingProducts' => $this->getProductTrends($application, ProductTrend::DIRECTION_FALLING),
            'forecasts' => $this->getForecasts($application),
            'opportunities' => $this->getOpportunities($application),
            'opportunityCounts' => $this->getOpportunityCounts($application),
        ]);
    }

    private function getSegmentBreakdown(Application $application): array
    {
        $labels = [
            'vip' => ['label' => 'VIP', 'icon' => '👑'],
            'engaged' => ['label' => 'Engajados', 'icon' => '🔥'],
            'converted' => ['label' => 'Convertidos', 'icon' => '✅'],
            'new' => ['label' => 'Novos', 'icon' => '✨'],
            'inactive' => ['label' => 'Inativos', 'icon' => '💤'],
        ];

        $contacts = Contact::where('tenant_id', $application->tenant_id)
            ->whereNotNull('segment')
            ->get(['segment', 'customer_score'])
            ->groupBy('segment');

        $total = max(1, $contacts->sum(fn ($group) => $group->count()));

        return collect($labels)->map(function ($meta, $key) use ($contacts, $total) {
            $group = $contacts->get($key);
            $count = $group ? $group->count() : 0;

            return array_merge($meta, [
                'key' => $key,
                'count' => $count,
                'percentage' => round(($count / $total) * 100, 1),
                'avg_score' => $group ? round((float) $group->avg('customer_score'), 1) : 0,
            ]);
        })->values()->all();
    }

    private function getProductTrends(Application $application, string $direction): Collection
    {
        $query = ProductTrend::where('application_id', $application->id)
            ->where('direction', $direction)
            ->limit(5);

        return $direction === ProductTrend::DIRECTION_RISING
            ? $query->orderByDesc('growth_rate')->get()
            : $query->orderBy('growth_rate')->get();
    }

    private function getForecasts(Application $application): Collection
    {
        return SalesForecast::where('application_id', $application->id)
            ->whereNull('seller_id')
            ->orderByDesc('forecast_date')
            ->limit(2)
            ->get()
            ->sortBy('horizon_days')
            ->values();
    }

    private function getOpportunities(Application $application): Collection
    {
        return Opportunity::where('application_id', $application->id)
            ->orderByDesc('score')
            ->limit(8)
            ->get();
    }

    private function getOpportunityCounts(Application $application): array
    {
        return Opportunity::where('application_id', $application->id)
            ->selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type')
            ->all();
    }
}
