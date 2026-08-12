<?php

namespace App\Livewire\Admin\Affiliate;

use App\Domain\Affiliate\Actions\ApproveProductOpportunityAction;
use App\Domain\Affiliate\Actions\PublishProductOpportunityAction;
use App\Domain\Affiliate\Actions\RejectProductOpportunityAction;
use App\Domain\Affiliate\Enums\StatusSprintA;
use App\Models\ProductOpportunity;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ProductOpportunityDetail extends Component
{
    public ProductOpportunity $opportunity;

    #[Validate('required|max:500')]
    public string $reason = '';

    public string $action = '';

    public bool $showModal = false;

    public function mount(ProductOpportunity $opportunity): void
    {
        $this->opportunity = $opportunity;
    }

    public function openApproveModal(): void
    {
        $this->action = 'approve';
        $this->reason = '';
        $this->showModal = true;
    }

    public function openRejectModal(): void
    {
        $this->action = 'reject';
        $this->reason = '';
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->action = '';
        $this->reason = '';
    }

    public function approve(): void
    {
        if ($this->opportunity->status_sprint_a !== StatusSprintA::DISCOVERED) {
            $this->addError('opportunity', 'Oportunidade deve estar em DISCOVERED para ser aprovada.');

            return;
        }

        try {
            $action = new ApproveProductOpportunityAction;
            $this->opportunity = $action->execute(
                opportunity: $this->opportunity,
                approver: auth()->user(),
                reason: $this->reason
            );

            $this->dispatch('opportunity-updated');
            $this->closeModal();
            session()->flash('success', 'Oportunidade aprovada com sucesso!');
        } catch (\Exception $e) {
            $this->addError('action', $e->getMessage());
        }
    }

    public function reject(): void
    {
        $this->validate(['reason' => 'required|max:500']);

        if ($this->opportunity->status_sprint_a !== StatusSprintA::DISCOVERED) {
            $this->addError('opportunity', 'Oportunidade deve estar em DISCOVERED para ser rejeitada.');

            return;
        }

        try {
            $action = new RejectProductOpportunityAction;
            $this->opportunity = $action->execute(
                opportunity: $this->opportunity,
                rejecter: auth()->user(),
                reason: $this->reason
            );

            $this->dispatch('opportunity-updated');
            $this->closeModal();
            session()->flash('success', 'Oportunidade rejeitada com sucesso!');
        } catch (\Exception $e) {
            $this->addError('action', $e->getMessage());
        }
    }

    public function publish(): void
    {
        if ($this->opportunity->status_sprint_a !== StatusSprintA::APPROVED) {
            $this->addError('opportunity', 'Oportunidade deve estar em APPROVED para ser publicada.');

            return;
        }

        try {
            $action = new PublishProductOpportunityAction;
            $this->opportunity = $action->execute($this->opportunity);

            $this->dispatch('opportunity-updated');
            session()->flash('success', 'Oportunidade publicada com sucesso!');
        } catch (\Exception $e) {
            $this->addError('action', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.affiliate.product-opportunity-detail', [
            'statuses' => StatusSprintA::cases(),
        ]);
    }
}
