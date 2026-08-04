<?php

namespace App\Livewire\Admin\Users;

use App\Application\Users\Actions\CreateUserAction;
use App\Application\Users\Actions\SyncUserRolesAction;
use App\Application\Users\Actions\UpdateUserAction;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Role;

#[Layout('layouts.admin')]
class UserForm extends Component
{
    public ?User $user = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    /**
     * @var list<string>
     */
    public array $selectedRoles = [];

    public function mount(?User $user = null): void
    {
        $this->user = $user;

        $this->authorize($this->user ? 'update' : 'create', $this->user ?? User::class);

        if ($this->user) {
            $this->name = $this->user->name;
            $this->email = $this->user->email;
            $this->selectedRoles = $this->user->getRoleNames()->all();
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($this->user?->id),
            ],
            'password' => [$this->user ? 'nullable' : 'required', 'string', 'min:8'],
            'selectedRoles' => ['array'],
            'selectedRoles.*' => ['string', 'exists:roles,name'],
        ];
    }

    public function save(CreateUserAction $createUser, UpdateUserAction $updateUser, SyncUserRolesAction $syncRoles): void
    {
        $this->validate();

        if ($this->user) {
            $updateUser->handle($this->user, $this->name, $this->email, $this->password ?: null);
            $syncRoles->handle($this->user, $this->selectedRoles);
        } else {
            $createUser->handle($this->name, $this->email, $this->password, $this->selectedRoles);
        }

        $this->redirectRoute('admin.users.index', navigate: false);
    }

    public function render(): View
    {
        return view('livewire.admin.users.user-form', [
            'availableRoles' => Role::query()->orderBy('name')->get(),
        ]);
    }
}
