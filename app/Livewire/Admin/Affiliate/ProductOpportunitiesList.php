<?php

namespace App\Livewire\Admin\Affiliate;

use App\Domain\Affiliate\Enums\StatusSprintA;
use App\Models\ProductOpportunity;
use Livewire\Component;
use Livewire\WithPagination;

class ProductOpportunitiesList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    public ?int $selectedOpportunity = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function selectOpportunity(?int $id): void
    {
        $this->selectedOpportunity = $id;
    }

    public function getOpportunitiesProperty()
    {
        $query = ProductOpportunity::byApplication(auth()->user()?->application_id ?? auth()->id());

        if ($this->search) {
            $query->whereHas('trend', function ($q) {
                $q->where('term', 'like', "%{$this->search}%");
            })->orWhereHas('affiliateProduct', function ($q) {
                $q->where('name', 'like', "%{$this->search}%");
            });
        }

        if ($this->statusFilter) {
            $query->byStatus($this->statusFilter);
        }

        $query->orderBy($this->sortBy, $this->sortDirection);

        return $query->paginate(15);
    }

    public function render()
    {
        return view('livewire.admin.affiliate.product-opportunities-list', [
            'opportunities' => $this->opportunities,
            'statuses' => StatusSprintA::cases(),
        ]);
    }
}
