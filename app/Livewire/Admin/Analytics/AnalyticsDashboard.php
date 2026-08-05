<?php

namespace App\Livewire\Admin\Analytics;

use App\Application\Analytics\Actions\GetConversionsAction;
use App\Application\Analytics\Actions\GetDashboardOverviewAction;
use App\Application\Analytics\Actions\GetFunnelAction;
use App\Application\Analytics\Actions\GetTopPagesAction;
use App\Application\Analytics\Actions\GetTopSubjectsAction;
use App\Application\Analytics\Actions\GetUtmBreakdownAction;
use App\Domain\Analytics\DateRange;
use App\Domain\Analytics\FunnelTemplates;
use App\Domain\Shared\Enums\Permission;
use App\Models\Application;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class AnalyticsDashboard extends Component
{
    public ?int $applicationId = null;

    public string $period = '7d';

    public string $funnelKey = 'site-pessoal';

    public function mount(): void
    {
        $this->authorize(Permission::AnalyticsView->value);

        $this->applicationId = Application::query()->where('is_active', true)->orderBy('name')->value('id')
            ?? Application::query()->orderBy('name')->value('id');
    }

    public function render(
        GetDashboardOverviewAction $overviewAction,
        GetTopPagesAction $topPagesAction,
        GetTopSubjectsAction $topSubjectsAction,
        GetUtmBreakdownAction $utmAction,
        GetFunnelAction $funnelAction,
        GetConversionsAction $conversionsAction,
    ): View {
        $applications = Application::query()->orderBy('name')->get();
        $application = $applications->firstWhere('id', $this->applicationId);
        $range = $this->dateRange();
        $funnelTemplate = FunnelTemplates::find($this->funnelKey);

        if (! $application) {
            return view('livewire.admin.analytics.analytics-dashboard', [
                'applications' => $applications,
                'application' => null,
            ]);
        }

        return view('livewire.admin.analytics.analytics-dashboard', [
            'applications' => $applications,
            'application' => $application,
            'overview' => $overviewAction->handle($application, $range),
            'topPages' => $topPagesAction->handle($application, $range),
            'topArticles' => $topSubjectsAction->handle($application, $range, 'article.viewed', 'Article'),
            'topServices' => $topSubjectsAction->handle($application, $range, 'service.viewed', 'Service'),
            'utmBreakdown' => $utmAction->handle($application, $range),
            'conversions' => $conversionsAction->handle($application, $range),
            'funnelTemplates' => FunnelTemplates::all(),
            'funnel' => $funnelTemplate ? $funnelAction->handle($application, $range, $funnelTemplate['steps']) : [],
        ]);
    }

    private function dateRange(): DateRange
    {
        return match ($this->period) {
            'today' => DateRange::today(),
            '30d' => DateRange::lastDays(30),
            '90d' => DateRange::lastDays(90),
            default => DateRange::lastDays(7),
        };
    }
}
