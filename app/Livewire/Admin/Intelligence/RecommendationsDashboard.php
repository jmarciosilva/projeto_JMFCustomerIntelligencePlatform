<?php

namespace App\Livewire\Admin\Intelligence;

use App\Actions\GenerateBusinessRecommendationsAction;
use App\Models\Application;
use App\Models\BusinessRecommendation;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class RecommendationsDashboard extends Component
{
    public ?int $applicationId = null;

    public ?int $sellerId = null;

    public bool $generating = false;

    public function mount(): void
    {
        $this->applicationId = Application::query()->where('is_active', true)->orderBy('name')->value('id')
            ?? Application::query()->orderBy('name')->value('id');

        $this->syncSellerId();
    }

    public function updatedApplicationId(): void
    {
        $this->sellerId = null;
        $this->syncSellerId();
    }

    public function generate(GenerateBusinessRecommendationsAction $action): void
    {
        if (! $this->applicationId) {
            return;
        }

        $this->generating = true;
        $action->execute($this->applicationId);
        $this->generating = false;
        $this->syncSellerId();
    }

    public function render(): View
    {
        $applications = Application::query()->orderBy('name')->get();
        $application = $applications->firstWhere('id', $this->applicationId);

        $sellerIds = $application
            ? BusinessRecommendation::where('application_id', $application->id)->distinct()->orderBy('seller_id')->pluck('seller_id')
            : collect();

        $recommendations = ($application && $this->sellerId)
            ? BusinessRecommendation::where('application_id', $application->id)
                ->forSeller($this->sellerId)
                ->orderByDesc('priority')
                ->get()
            : collect();

        return view('livewire.admin.intelligence.recommendations-dashboard', [
            'applications' => $applications,
            'application' => $application,
            'sellerIds' => $sellerIds,
            'recommendations' => $recommendations,
        ]);
    }

    private function syncSellerId(): void
    {
        if ($this->sellerId || ! $this->applicationId) {
            return;
        }

        $this->sellerId = BusinessRecommendation::where('application_id', $this->applicationId)
            ->orderBy('seller_id')
            ->value('seller_id');
    }
}
