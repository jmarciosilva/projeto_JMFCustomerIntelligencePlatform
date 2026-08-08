<?php

namespace App\Livewire\Marketplace;

use App\Models\Contact;
use App\Models\Event;
use App\Models\Tenant;
use Livewire\Component;
use Livewire\WithPagination;

class ContactsList extends Component
{
    use WithPagination;

    public Tenant $tenant;

    public string $searchTerm = '';

    public string $sortBy = 'lead_score';

    public string $sortDirection = 'desc';

    public string $filterBy = 'all'; // all, converted, pending, abandoned

    protected $queryString = ['searchTerm', 'sortBy', 'sortDirection', 'filterBy'];

    public function mount(Tenant $tenant)
    {
        $this->tenant = $tenant;
    }

    public function updatingSearchTerm()
    {
        $this->resetPage();
    }

    public function updatingFilterBy()
    {
        $this->resetPage();
    }

    public function updatingExcept($name)
    {
        if (in_array($name, ['sortBy', 'sortDirection'])) {
            $this->resetPage();
        }
    }

    public function changeSortBy($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'desc';
        }
    }

    public function render()
    {
        $query = Contact::where('tenant_id', $this->tenant->id);

        // Filtro de busca
        if ($this->searchTerm) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->searchTerm}%")
                    ->orWhere('email', 'like', "%{$this->searchTerm}%");
            });
        }

        // Filtro por status
        if ($this->filterBy !== 'all') {
            $query = $this->applyStatusFilter($query, $this->filterBy);
        }

        // Ordenação
        $query->orderBy($this->sortBy, $this->sortDirection);

        $contacts = $query->paginate(15);

        // Enriquecer contatos com info de status
        $contacts->each(function ($contact) {
            $contact->status = $this->determineContactStatus($contact);
            $contact->event_count = Event::where('contact_id', $contact->id)->count();
        });

        return view('livewire.marketplace.contacts-list', [
            'contacts' => $contacts,
        ]);
    }

    private function applyStatusFilter($query, $status)
    {
        $convertedContactIds = Event::whereIn('event_name', ['purchase.completed'])
            ->distinct()
            ->pluck('contact_id')
            ->toArray();

        $abandonedContactIds = Event::where('event_name', 'cart.abandoned')
            ->whereNotIn('contact_id', $convertedContactIds)
            ->distinct()
            ->pluck('contact_id')
            ->toArray();

        match ($status) {
            'converted' => $query->whereIn('id', $convertedContactIds),
            'abandoned' => $query->whereIn('id', $abandonedContactIds),
            'pending' => $query->whereNotIn('id', array_merge($convertedContactIds, $abandonedContactIds)),
            default => $query,
        };

        return $query;
    }

    private function determineContactStatus($contact): string
    {
        $events = Event::where('contact_id', $contact->id)->pluck('event_name')->toArray();

        if (in_array('purchase.completed', $events)) {
            return 'converted';
        }

        if (in_array('cart.abandoned', $events)) {
            return 'abandoned';
        }

        return 'pending';
    }
}
