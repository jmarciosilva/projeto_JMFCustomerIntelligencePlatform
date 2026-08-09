<?php

namespace App\Livewire\Admin\Trends;

use App\Application\Trends\Actions\CollectTrendSignalsAction;
use App\Models\Trend;
use App\Models\Watchlist;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class WatchlistShow extends Component
{
    public Watchlist $watchlist;

    public function mount(Watchlist $watchlist): void
    {
        $this->authorize('view', $watchlist);

        $this->watchlist = $watchlist;
    }

    public function collectNow(Trend $trend, CollectTrendSignalsAction $action): void
    {
        $this->authorize('update', $trend);

        $action->handle($trend);
    }

    public function render(): View
    {
        $trends = $this->watchlist->trends()
            ->withCount('snapshots')
            ->orderBy('term')
            ->get();

        return view('livewire.admin.trends.watchlist-show', [
            'trends' => $trends,
        ]);
    }
}
