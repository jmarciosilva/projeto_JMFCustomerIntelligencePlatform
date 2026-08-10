<?php

namespace App\Livewire\Admin\Affiliate;

use App\Models\AffiliateConversion;
use Livewire\Component;
use Livewire\WithPagination;

class ConversionIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status_filter = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function delete(AffiliateConversion $conversion): void
    {
        $conversion->delete();
        session()->flash('message', 'Conversão deletada com sucesso.');
    }

    public function approve(AffiliateConversion $conversion): void
    {
        $conversion->approve();
        session()->flash('message', 'Conversão aprovada com sucesso.');
    }

    public function markAsPaid(AffiliateConversion $conversion): void
    {
        $conversion->markAsPaid();
        session()->flash('message', 'Conversão marcada como paga.');
    }

    public function cancel(AffiliateConversion $conversion): void
    {
        $conversion->cancel();
        session()->flash('message', 'Conversão cancelada.');
    }

    public function render()
    {
        $query = AffiliateConversion::query()
            ->with('affiliateProduct', 'campaign', 'affiliateProgram')
            ->where('order_reference', 'like', "%{$this->search}%");

        if ($this->status_filter) {
            $query->where('status', $this->status_filter);
        }

        $conversions = $query->orderByDesc('order_date')->paginate(20);

        return view('livewire.admin.affiliate.conversion-index', [
            'conversions' => $conversions,
        ]);
    }
}
