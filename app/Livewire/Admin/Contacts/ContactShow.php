<?php

namespace App\Livewire\Admin\Contacts;

use App\Application\Timeline\Actions\GetContactTimelineAction;
use App\Models\Contact;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class ContactShow extends Component
{
    use WithPagination;

    public Contact $contact;

    public function mount(Contact $contact): void
    {
        $this->contact = $contact;

        $this->authorize('view', $this->contact);
    }

    public function render(GetContactTimelineAction $action): View
    {
        return view('livewire.admin.contacts.contact-show', [
            'events' => $action->handle($this->contact),
            'consents' => $this->contact->consents()->orderBy('purpose')->get(),
        ]);
    }
}
