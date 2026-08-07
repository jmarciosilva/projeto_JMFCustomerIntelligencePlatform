<?php

namespace JmfSystem\CustomerIntelligence\Livewire\Plugins\JmfCi;

use JmfSystem\CustomerIntelligence\Services\JmfCiApiClient;
use Livewire\Component;

class Dashboard extends Component
{
    public string $period = '7'; // 7, 30, 90 dias, ou 'today'

    private JmfCiApiClient $apiClient;

    public function mount(JmfCiApiClient $apiClient): void
    {
        $this->apiClient = $apiClient;
    }

    public function setPeriod(string $period): void
    {
        $this->period = $period;
    }

    public function getDateRangeProperty(): array
    {
        $endDate = now();
        $startDate = match ($this->period) {
            'today' => now()->startOfDay(),
            '7' => now()->subDays(7),
            '30' => now()->subDays(30),
            '90' => now()->subDays(90),
            default => now()->subDays(7),
        };

        return [
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
        ];
    }

    public function getMetricsProperty(): array
    {
        return $this->apiClient->getMetrics($this->dateRange);
    }

    public function getRecentContactsProperty(): array
    {
        return $this->apiClient->getContacts([
            'per_page' => 5,
            'page' => 1,
            'start_date' => $this->dateRange['start_date'],
            'end_date' => $this->dateRange['end_date'],
        ])['data'] ?? [];
    }

    public function getRecentEventsProperty(): array
    {
        return $this->apiClient->getEvents([
            'per_page' => 10,
            'page' => 1,
            'start_date' => $this->dateRange['start_date'],
            'end_date' => $this->dateRange['end_date'],
        ])['data'] ?? [];
    }

    public function render()
    {
        return view('plugins.jmf-ci.dashboard', [
            'metrics' => $this->metrics,
            'recentContacts' => $this->recentContacts,
            'recentEvents' => $this->recentEvents,
            'dateRange' => $this->dateRange,
        ]);
    }
}
