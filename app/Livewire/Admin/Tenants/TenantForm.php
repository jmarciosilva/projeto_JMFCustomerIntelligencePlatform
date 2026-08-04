<?php

namespace App\Livewire\Admin\Tenants;

use App\Application\Tenants\Actions\CreateTenantAction;
use App\Application\Tenants\Actions\UpdateTenantAction;
use App\Models\Tenant;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class TenantForm extends Component
{
    public ?Tenant $tenant = null;

    public string $name = '';

    public function mount(?Tenant $tenant = null): void
    {
        $this->tenant = $tenant;

        $this->authorize($this->tenant ? 'update' : 'create', $this->tenant ?? Tenant::class);

        if ($this->tenant) {
            $this->name = $this->tenant->name;
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }

    public function save(CreateTenantAction $createTenant, UpdateTenantAction $updateTenant): void
    {
        $this->validate();

        if ($this->tenant) {
            $updateTenant->handle($this->tenant, $this->name);
        } else {
            $createTenant->handle($this->name);
        }

        $this->redirectRoute('admin.tenants.index', navigate: false);
    }

    public function render(): View
    {
        return view('livewire.admin.tenants.tenant-form');
    }
}
