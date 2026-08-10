<?php

namespace App\Livewire\Admin\Affiliate;

use App\Domain\Affiliate\JmfRecommendationEngineAction;
use Livewire\Component;

class RecommendationDashboard extends Component
{
    public array $recommendations = [];
    public int $limit = 15;

    public function mount(): void
    {
        $this->loadRecommendations();
    }

    public function loadRecommendations(): void
    {
        $action = app(JmfRecommendationEngineAction::class);
        $this->recommendations = $action->execute(auth()->user()->application, $this->limit);
    }

    public function render()
    {
        return view('livewire.admin.affiliate.recommendation-dashboard');
    }
}
