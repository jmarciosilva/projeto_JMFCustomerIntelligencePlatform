<?php

namespace App\Livewire\Admin\Affiliate;

use App\Models\Campaign;
use App\Models\ContentPublication;
use Livewire\Component;
use Livewire\WithPagination;

class ContentPublicationIndex extends Component
{
    use WithPagination;

    public ?int $campaign_id = null;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCampaignId(): void
    {
        $this->resetPage();
    }

    public function delete(ContentPublication $content): void
    {
        $content->delete();
        session()->flash('message', 'Conteúdo deletado com sucesso.');
    }

    public function render()
    {
        $app = auth()->user()->application ?? app(Campaign::class)->first()?->application;

        $query = ContentPublication::query()
            ->with('campaign', 'productOpportunity')
            ->where('title', 'like', "%{$this->search}%");

        if ($this->campaign_id) {
            $query->where('campaign_id', $this->campaign_id);
        }

        $contents = $query->orderByDesc('created_at')->paginate(15);

        $campaigns = Campaign::where('application_id', $app?->id)
            ->orderBy('name')
            ->get();

        return view('livewire.admin.affiliate.content-publication-index', [
            'contents' => $contents,
            'campaigns' => $campaigns,
        ])->layout('layouts.admin', ['header' => 'Administração']);
    }
}
