<?php

namespace App\Livewire\Admin\Users;

use App\Application\Users\Actions\DeleteUserAction;
use App\Application\Users\Actions\ToggleUserActiveAction;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class UserIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function mount(): void
    {
        $this->authorize('viewAny', User::class);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function toggleActive(User $user, ToggleUserActiveAction $action): void
    {
        $this->authorize('update', $user);

        $action->handle($user);
    }

    public function delete(User $user, DeleteUserAction $action): void
    {
        $this->authorize('delete', $user);

        $action->handle($user);
    }

    public function render(): View
    {
        $users = User::query()
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($query): void {
                    $query->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->with('roles')
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.admin.users.user-index', [
            'users' => $users,
        ]);
    }
}
