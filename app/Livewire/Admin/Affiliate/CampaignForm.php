<?php

namespace App\Livewire\Admin\Affiliate;

use App\Models\Application;
use App\Models\Campaign;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class CampaignForm extends Component
{
    public ?Campaign $campaign = null;

    public string $name = '';

    public string $description = '';

    public string $status = Campaign::STATUS_ACTIVE;

    public string $start_date = '';

    public string $end_date = '';

    public int $expected_clicks = 0;

    public int $expected_conversions = 0;

    public function mount(?Campaign $campaign = null): void
    {
        if ($campaign) {
            $this->campaign = $campaign;
            $this->name = $campaign->name;
            $this->description = $campaign->description ?? '';
            $this->status = $campaign->status;
            $this->start_date = $campaign->start_date?->toDateString() ?? '';
            $this->end_date = $campaign->end_date?->toDateString() ?? '';
            $this->expected_clicks = $campaign->expected_clicks ?? 0;
            $this->expected_conversions = $campaign->expected_conversions ?? 0;
        } else {
            $this->start_date = now()->toDateString();
            $this->end_date = now()->addDays(30)->toDateString();
        }
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,paused,archived',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'expected_clicks' => 'required|integer|min:0',
            'expected_conversions' => 'required|integer|min:0',
        ]);

        $app = auth()->user()->application ?? Application::first();

        if ($this->campaign) {
            $this->campaign->update([
                'name' => $this->name,
                'description' => $this->description,
                'status' => $this->status,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'expected_clicks' => $this->expected_clicks,
                'expected_conversions' => $this->expected_conversions,
            ]);
            session()->flash('message', 'Campanha atualizada com sucesso.');
        } else {
            Campaign::create([
                'application_id' => $app->id,
                'name' => $this->name,
                'description' => $this->description,
                'status' => $this->status,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'expected_clicks' => $this->expected_clicks,
                'expected_conversions' => $this->expected_conversions,
            ]);
            session()->flash('message', 'Campanha criada com sucesso.');
            redirect()->route('admin.affiliate.campaigns.index');
        }
    }

    public function render()
    {
        return view('livewire.admin.affiliate.campaign-form');
    }
}
