<?php

namespace App\Livewire\Admin\Applications;

use App\Application\Applications\Actions\DeleteApplicationAction;
use App\Application\Applications\Actions\ToggleApplicationActiveAction;
use App\Models\Application;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class ApplicationIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Application::class);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function toggleActive(Application $application, ToggleApplicationActiveAction $action): void
    {
        $this->authorize('update', $application);

        $action->handle($application);
    }

    public function delete(Application $application, DeleteApplicationAction $action): void
    {
        $this->authorize('delete', $application);

        $action->handle($application);
    }

    public function render(): View
    {
        $applications = Application::query()
            ->when($this->search !== '', function ($query): void {
                $query->where('name', 'like', "%{$this->search}%");
            })
            ->with('tenant')
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.admin.applications.application-index', [
            'applications' => $applications,
        ]);
    }
}
