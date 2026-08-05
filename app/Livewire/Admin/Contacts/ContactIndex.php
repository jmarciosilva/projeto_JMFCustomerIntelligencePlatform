<?php

namespace App\Livewire\Admin\Contacts;

use App\Models\Contact;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class ContactIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Contact::class);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $contacts = Contact::query()
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($inner): void {
                    $inner->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%")
                        ->orWhere('external_id', 'like', "%{$this->search}%");
                });
            })
            ->orderByDesc('last_seen_at')
            ->paginate(15);

        return view('livewire.admin.contacts.contact-index', [
            'contacts' => $contacts,
        ]);
    }
}
