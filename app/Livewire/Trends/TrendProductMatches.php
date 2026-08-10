<?php

namespace App\Livewire\Trends;

use App\Models\Trend;
use Livewire\Component;
use Livewire\WithPagination;

class TrendProductMatches extends Component
{
    use WithPagination;

    public Trend $trend;

    public function render()
    {
        $matches = $this->trend->productMatches()
            ->with('product', 'product.affiliateProgram')
            ->orderByDesc('match_score')
            ->paginate(10);

        return view('livewire.trends.trend-product-matches', [
            'matches' => $matches,
        ]);
    }
}
