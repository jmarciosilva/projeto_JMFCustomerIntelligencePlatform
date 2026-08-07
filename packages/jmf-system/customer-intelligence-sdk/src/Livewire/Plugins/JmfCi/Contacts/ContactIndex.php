<?php

namespace JmfSystem\CustomerIntelligence\Livewire\Plugins\JmfCi\Contacts;

use JmfSystem\CustomerIntelligence\Services\JmfCiApiClient;
use Livewire\Component;
use Livewire\WithPagination;

class ContactIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $period = '30';

    private JmfCiApiClient $apiClient;

    public function mount(JmfCiApiClient $apiClient): void
    {
        $this->apiClient = $apiClient;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPeriod(): void
    {
        $this->resetPage();
    }

    public function getDateRangeProperty(): array
    {
        $endDate = now();
        $startDate = match ($this->period) {
            'today' => now()->startOfDay(),
            '7' => now()->subDays(7),
            '30' => now()->subDays(30),
            '90' => now()->subDays(90),
            default => now()->subDays(30),
        };

        return [
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
        ];
    }

    public function getContactsProperty(): array
    {
        return $this->apiClient->getContacts([
            'page' => $this->currentPage,
            'per_page' => 25,
            'search' => $this->search ?: null,
            'start_date' => $this->dateRange['start_date'],
            'end_date' => $this->dateRange['end_date'],
        ]);
    }

    public function render()
    {
        $response = $this->contacts;

        return view('plugins.jmf-ci.contacts.index', [
            'contacts' => $response['data'] ?? [],
            'total' => $response['total'] ?? 0,
            'currentPage' => $this->currentPage,
            'perPage' => 25,
        ]);
    }
}
