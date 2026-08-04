<?php

namespace App\Livewire\Admin\Tenants;

use App\Application\Tenants\Actions\DeleteTenantAction;
use App\Application\Tenants\Actions\ToggleTenantActiveAction;
use App\Models\Tenant;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class TenantIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Tenant::class);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function toggleActive(Tenant $tenant, ToggleTenantActiveAction $action): void
    {
        $this->authorize('update', $tenant);

        $action->handle($tenant);
    }

    public function delete(Tenant $tenant, DeleteTenantAction $action): void
    {
        $this->authorize('delete', $tenant);

        try {
            $action->handle($tenant);
        } catch (ValidationException $exception) {
            $this->addError('tenant', $exception->validator->errors()->first('tenant'));
        }
    }

    public function render(): View
    {
        $tenants = Tenant::query()
            ->when($this->search !== '', function ($query): void {
                $query->where('name', 'like', "%{$this->search}%");
            })
            ->withCount('applications')
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.admin.tenants.tenant-index', [
            'tenants' => $tenants,
        ]);
    }
}
