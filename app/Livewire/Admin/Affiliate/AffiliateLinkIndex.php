<?php

namespace App\Livewire\Admin\Affiliate;

use App\Models\AffiliateLink;
use Livewire\Component;
use Livewire\WithPagination;

class AffiliateLinkIndex extends Component
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

    public function delete(AffiliateLink $link): void
    {
        $link->delete();
        session()->flash('message', 'Link deletado com sucesso.');
    }

    public function copySlug(string $slug): void
    {
        session()->flash('slug_copied', $slug);
    }

    public function render()
    {
        $query = AffiliateLink::query()
            ->with('campaign', 'affiliateProduct')
            ->where('slug', 'like', "%{$this->search}%");

        if ($this->status_filter) {
            $query->where('status', $this->status_filter);
        }

        $links = $query->orderByDesc('created_at')->paginate(20);

        return view('livewire.admin.affiliate.affiliate-link-index', [
            'links' => $links,
        ]);
    }
}
