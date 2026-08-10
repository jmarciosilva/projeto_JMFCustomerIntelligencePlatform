<?php

namespace App\Livewire\Admin\Affiliate;

use App\Application\Affiliate\Actions\DeleteAffiliateProgramAction;
use App\Models\AffiliateProgram;
use App\Models\Application;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class ProgramIndex extends Component
{
    use WithPagination;

    public ?int $applicationId = null;

    public string $search = '';

    public function mount(): void
    {
        // $this->authorize('viewAny', AffiliateProgram::class);

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

    public function delete(AffiliateProgram $program, DeleteAffiliateProgramAction $action): void
    {
        // $this->authorize('delete', $program);

        try {
            $action->handle($program);
        } catch (ValidationException $exception) {
            $this->addError('affiliate_program', $exception->validator->errors()->first('affiliate_program'));
        }
    }

    public function render(): View
    {
        $programs = AffiliateProgram::query()
            ->when($this->applicationId, fn ($query) => $query->where('application_id', $this->applicationId))
            ->when($this->search !== '', function ($query): void {
                $query->where('name', 'like', "%{$this->search}%");
            })
            ->withCount('products')
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.admin.affiliate.program-index', [
            'programs' => $programs,
            'applications' => Application::query()->orderBy('name')->get(),
        ]);
    }
}
