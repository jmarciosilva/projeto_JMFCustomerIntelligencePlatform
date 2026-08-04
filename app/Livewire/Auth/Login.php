<?php

namespace App\Livewire\Auth;

use App\Application\Auth\Actions\AuthenticateAdminAction;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.guest')]
class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(AuthenticateAdminAction $action): void
    {
        $this->validate();

        $action->handle($this->email, $this->password, $this->remember);

        $this->redirectRoute('admin.dashboard', navigate: false);
    }

    public function render(): View
    {
        return view('livewire.auth.login');
    }
}
