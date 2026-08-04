<?php

namespace App\Livewire\Admin\Applications;

use App\Application\Applications\Actions\CreateApplicationAction;
use App\Application\Applications\Actions\UpdateApplicationAction;
use App\Models\Application;
use App\Models\Tenant;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class ApplicationForm extends Component
{
    public ?Application $application = null;

    public string $name = '';

    public ?int $tenant_id = null;

    public function mount(?Application $application = null): void
    {
        $this->application = $application;

        $this->authorize($this->application ? 'update' : 'create', $this->application ?? Application::class);

        if ($this->application) {
            $this->name = $this->application->name;
            $this->tenant_id = $this->application->tenant_id;
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'tenant_id' => ['required', 'integer', 'exists:tenants,id'],
        ];
    }

    public function save(CreateApplicationAction $createApplication, UpdateApplicationAction $updateApplication): void
    {
        $this->validate();

        if ($this->application) {
            $updateApplication->handle($this->application, $this->name);
        } else {
            /** @var Tenant $tenant */
            $tenant = Tenant::query()->findOrFail($this->tenant_id);

            $createApplication->handle($tenant, $this->name);
        }

        $this->redirectRoute('admin.applications.index', navigate: false);
    }

    public function render(): View
    {
        return view('livewire.admin.applications.application-form', [
            'tenants' => Tenant::query()->orderBy('name')->get(),
        ]);
    }
}
