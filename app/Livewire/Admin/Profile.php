<?php

namespace App\Livewire\Admin;

use App\Application\Users\Actions\UpdateProfileAction;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Profile extends Component
{
    public string $name = '';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public bool $saved = false;

    public function mount(): void
    {
        /** @var User $user */
        $user = Auth::user();

        $this->name = $user->name;
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'current_password' => ['nullable', 'required_with:password', 'current_password:web'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function save(UpdateProfileAction $action): void
    {
        $this->validate();

        /** @var User $user */
        $user = Auth::user();

        $action->handle($user, $this->name, $this->password ?: null);

        $this->reset(['current_password', 'password', 'password_confirmation']);

        $this->saved = true;
    }

    public function render(): View
    {
        return view('livewire.admin.profile');
    }
}
