<?php

namespace App\Livewire\Admin\Affiliate;

use App\Models\Campaign;
use Livewire\Component;
use Livewire\WithPagination;

class CampaignIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(Campaign $campaign): void
    {
        $campaign->delete();
        session()->flash('message', 'Campanha deletada com sucesso.');
    }

    public function render()
    {
        $campaigns = Campaign::query()
            ->where('name', 'like', "%{$this->search}%")
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.admin.affiliate.campaign-index', [
            'campaigns' => $campaigns,
        ]);
    }
}
