<?php

namespace App\Livewire\Admin\Affiliate;

use App\Application\Affiliate\Actions\CreateAffiliateProgramAction;
use App\Application\Affiliate\Actions\UpdateAffiliateProgramAction;
use App\Models\AffiliateProgram;
use App\Models\Application;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class ProgramForm extends Component
{
    public ?AffiliateProgram $program = null;

    public ?int $applicationId = null;

    public string $name = '';

    public string $website = '';

    public string $description = '';

    public string $status = AffiliateProgram::STATUS_ACTIVE;

    public function mount(?AffiliateProgram $program = null): void
    {
        $this->program = $program;

        // $this->authorize($this->program ? 'update' : 'create', $this->program ?? AffiliateProgram::class);

        if ($this->program) {
            $this->applicationId = $this->program->application_id;
            $this->name = $this->program->name;
            $this->website = (string) $this->program->website;
            $this->description = (string) $this->program->description;
            $this->status = $this->program->status;
        } else {
            $this->applicationId = Application::query()->where('is_active', true)->orderBy('name')->value('id')
                ?? Application::query()->orderBy('name')->value('id');
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'applicationId' => ['required', 'integer', 'exists:applications,id'],
            'name' => ['required', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'in:'.AffiliateProgram::STATUS_ACTIVE.','.AffiliateProgram::STATUS_INACTIVE],
        ];
    }

    public function save(CreateAffiliateProgramAction $createAction, UpdateAffiliateProgramAction $updateAction): void
    {
        $data = $this->validate();

        if ($this->program) {
            $updateAction->handle($this->program, $data);
        } else {
            $application = Application::findOrFail($this->applicationId);
            $createAction->handle($application, $data);
        }

        $this->redirectRoute('admin.affiliate.programs.index', navigate: false);
    }

    public function render(): View
    {
        return view('livewire.admin.affiliate.program-form', [
            'applications' => Application::query()->orderBy('name')->get(),
        ]);
    }
}
