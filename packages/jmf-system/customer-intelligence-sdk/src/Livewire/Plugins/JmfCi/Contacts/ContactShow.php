<?php

namespace JmfSystem\CustomerIntelligence\Livewire\Plugins\JmfCi\Contacts;

use Livewire\Component;
use Livewire\WithPagination;
use JmfSystem\CustomerIntelligence\Services\JmfCiApiClient;

class ContactShow extends Component
{
    use WithPagination;

    public mixed $contactId;

    private JmfCiApiClient $apiClient;

    public function mount(mixed $contactId, JmfCiApiClient $apiClient): void
    {
        $this->contactId = $contactId;
        $this->apiClient = $apiClient;
    }

    public function getContactProperty(): ?array
    {
        return $this->apiClient->getContact($this->contactId);
    }

    public function getEventsProperty(): array
    {
        return $this->apiClient->getContactEvents($this->contactId, [
            'page' => $this->currentPage,
            'per_page' => 25,
        ]);
    }

    public function render()
    {
        $contact = $this->contact;
        $eventsResponse = $this->events;

        return view('plugins.jmf-ci.contacts.show', [
            'contact' => $contact,
            'events' => $eventsResponse['data'] ?? [],
            'total' => $eventsResponse['total'] ?? 0,
            'currentPage' => $this->currentPage,
            'perPage' => 25,
        ]);
    }
}
