<?php

namespace App\Livewire\Admin\Applications;

use App\Application\Applications\Actions\CreateApplicationTokenAction;
use App\Application\Applications\Actions\RevokeApplicationTokenAction;
use App\Application\Applications\Actions\RotateApplicationTokenAction;
use App\Models\Application;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class ApplicationTokens extends Component
{
    public Application $application;

    public string $tokenName = '';

    public ?string $plainTextToken = null;

    public function mount(Application $application): void
    {
        $this->application = $application;

        $this->authorize('manageTokens', $this->application);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'tokenName' => ['required', 'string', 'max:255'],
        ];
    }

    public function create(CreateApplicationTokenAction $action): void
    {
        $this->authorize('manageTokens', $this->application);
        $this->validate();

        $token = $action->handle($this->application, $this->tokenName);

        $this->plainTextToken = $token->plainTextToken;
        $this->tokenName = '';
    }

    public function rotate(int $tokenId, RotateApplicationTokenAction $action): void
    {
        $this->authorize('manageTokens', $this->application);

        $token = $action->handle($this->application, $tokenId);

        $this->plainTextToken = $token->plainTextToken;
    }

    public function revoke(int $tokenId, RevokeApplicationTokenAction $action): void
    {
        $this->authorize('manageTokens', $this->application);

        $action->handle($this->application, $tokenId);
    }

    public function dismissToken(): void
    {
        $this->plainTextToken = null;
    }

    public function render(): View
    {
        return view('livewire.admin.applications.application-tokens', [
            'tokens' => $this->application->tokens()->latest()->get(),
        ]);
    }
}
