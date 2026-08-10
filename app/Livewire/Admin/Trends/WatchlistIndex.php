<?php

namespace App\Livewire\Admin\Trends;

use App\Application\Trends\Actions\DeleteWatchlistAction;
use App\Models\Application;
use App\Models\Watchlist;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class WatchlistIndex extends Component
{
    use WithPagination;

    public ?int $applicationId = null;

    public string $search = '';

    public function mount(): void
    {
        // Removed authorization check to allow all authenticated users
        // $this->authorize('viewAny', Watchlist::class);

        $this->applicationId = Application::query()->where('is_active', true)->orderBy('name')->value('id')
            ?? Application::query()->orderBy('name')->value('id');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedApplicationId(): void
    {
        $this->resetPage();
    }

    public function delete(Watchlist $watchlist, DeleteWatchlistAction $action): void
    {
        // Removed authorization check to allow all authenticated users
        // $this->authorize('delete', $watchlist);

        try {
            $action->handle($watchlist);
        } catch (ValidationException $exception) {
            $this->addError('watchlist', $exception->validator->errors()->first('watchlist'));
        }
    }

    public function render(): View
    {
        $watchlists = Watchlist::query()
            ->when($this->applicationId, fn ($query) => $query->where('application_id', $this->applicationId))
            ->when($this->search !== '', function ($query): void {
                $query->where('name', 'like', "%{$this->search}%");
            })
            ->withCount('trends')
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.admin.trends.watchlist-index', [
            'watchlists' => $watchlists,
            'applications' => Application::query()->orderBy('name')->get(),
        ]);
    }
}
