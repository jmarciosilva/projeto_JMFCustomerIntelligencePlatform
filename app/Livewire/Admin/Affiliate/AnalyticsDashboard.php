<?php

namespace App\Livewire\Admin\Affiliate;

use App\Domain\Affiliate\CalculateAffiliateMetricsAction;
use App\Domain\Affiliate\GetTopAffiliateContentAction;
use App\Domain\Affiliate\GetTopAffiliateProductsAction;
use App\Models\Application;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class AnalyticsDashboard extends Component
{
    public string $period = '30';

    public array $metrics = [];

    public array $topProducts = [];

    public array $topContent = [];

    protected CalculateAffiliateMetricsAction $metricsAction;

    protected GetTopAffiliateProductsAction $productsAction;

    protected GetTopAffiliateContentAction $contentAction;

    public function mount(): void
    {
        $this->metricsAction = app(CalculateAffiliateMetricsAction::class);
        $this->productsAction = app(GetTopAffiliateProductsAction::class);
        $this->contentAction = app(GetTopAffiliateContentAction::class);

        $this->updateMetrics();
    }

    public function updatingPeriod(): void
    {
        $this->updateMetrics();
    }

    public function updateMetrics(): void
    {
        $app = auth()->user()->application ?? Application::first();

        $startDate = match ($this->period) {
            '7' => now()->subDays(7),
            '30' => now()->subDays(30),
            '90' => now()->subDays(90),
            '365' => now()->subYear(),
            default => now()->subDays(30),
        };

        $this->metrics = $this->metricsAction->execute($app, $startDate, now());
        $this->topProducts = $this->productsAction->execute($app, $startDate, now());
        $this->topContent = $this->contentAction->execute($app, $startDate, now());
    }

    public function render()
    {
        return view('livewire.admin.affiliate.analytics-dashboard');
    }
}
