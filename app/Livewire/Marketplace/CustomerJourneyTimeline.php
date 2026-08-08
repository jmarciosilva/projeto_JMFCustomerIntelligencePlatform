<?php

namespace App\Livewire\Marketplace;

use App\Models\Contact;
use App\Models\Event;
use Livewire\Component;

class CustomerJourneyTimeline extends Component
{
    public Contact $contact;

    public array $journey = [];

    public array $stages = [];

    public string $conversionStatus = 'pending'; // pending, converted, abandoned

    public function mount(Contact $contact)
    {
        $this->contact = $contact;
        $this->loadJourney();
    }

    private function loadJourney(): void
    {
        $events = Event::where('contact_id', $this->contact->id)
            ->orderBy('occurred_at', 'asc')
            ->get();

        $this->journey = $events->map(function ($event) {
            return [
                'event_name' => $event->event_name,
                'display_name' => $this->getEventDisplayName($event->event_name),
                'icon' => $this->getEventIcon($event->event_name),
                'color' => $this->getEventColor($event->event_name),
                'occurred_at' => $event->occurred_at->format('d/m/Y H:i'),
                'timestamp' => $event->occurred_at->timestamp,
                'product_id' => $event->properties?->get('product_id'),
                'seller_id' => $event->properties?->get('seller_id'),
                'value' => $event->properties?->get('total_value'),
            ];
        })->toArray();

        $this->stages = $this->categorizeJourney($events);
        $this->conversionStatus = $this->determineConversionStatus($events);
    }

    private function getEventDisplayName(string $eventName): string
    {
        return match ($eventName) {
            'product.viewed' => 'Visualizou Produto',
            'product.search' => 'Buscou Produto',
            'product.filtered' => 'Aplicou Filtro',
            'product.favorited' => 'Favoritou Produto',
            'product.unfavorited' => 'Removeu Favorito',
            'cart.item_added' => 'Adicionou ao Carrinho',
            'cart.item_removed' => 'Removeu do Carrinho',
            'cart.viewed' => 'Visualizou Carrinho',
            'cart.abandoned' => 'Abandonou Carrinho',
            'checkout.started' => 'Iniciou Checkout',
            'purchase.completed' => 'Compra Realizada ✓',
            'purchase.cancelled' => 'Compra Cancelada',
            'review.submitted' => 'Deixou Avaliação',
            'seller.contacted' => 'Contatou Vendedor',
            'seller.profile_viewed' => 'Visualizou Perfil',
            'social_media.clicked' => 'Clicou em Rede Social',
            default => str($eventName)->title(),
        };
    }

    private function getEventIcon(string $eventName): string
    {
        return match ($eventName) {
            'product.viewed', 'product.search' => '👁️',
            'product.filtered' => '🔍',
            'product.favorited' => '❤️',
            'product.unfavorited' => '🤍',
            'cart.item_added' => '➕',
            'cart.item_removed' => '➖',
            'cart.viewed' => '🛒',
            'cart.abandoned' => '⏸️',
            'checkout.started' => '💳',
            'purchase.completed' => '✅',
            'purchase.cancelled' => '❌',
            'review.submitted' => '⭐',
            'seller.contacted' => '💬',
            'seller.profile_viewed' => '👤',
            'social_media.clicked' => '🔗',
            default => '📌',
        };
    }

    private function getEventColor(string $eventName): string
    {
        return match ($eventName) {
            'product.viewed', 'product.search', 'product.filtered' => 'blue',
            'product.favorited' => 'red',
            'product.unfavorited' => 'gray',
            'cart.item_added' => 'green',
            'cart.item_removed' => 'red',
            'cart.viewed' => 'yellow',
            'cart.abandoned' => 'orange',
            'checkout.started' => 'purple',
            'purchase.completed' => 'emerald',
            'purchase.cancelled' => 'red',
            'review.submitted' => 'amber',
            'seller.contacted' => 'indigo',
            'seller.profile_viewed' => 'cyan',
            default => 'gray',
        };
    }

    private function categorizeJourney($events): array
    {
        $eventNames = $events->pluck('event_name')->unique()->toArray();
        $stages = [];

        $stageDefinitions = [
            'awareness' => ['product.viewed', 'product.search', 'seller.profile_viewed'],
            'interest' => ['product.filtered', 'product.favorited'],
            'decision' => ['cart.item_added', 'cart.viewed'],
            'action' => ['checkout.started', 'purchase.completed'],
            'advocacy' => ['review.submitted', 'social_media.clicked'],
        ];

        foreach ($stageDefinitions as $stage => $events_list) {
            if (count(array_intersect($eventNames, $events_list)) > 0) {
                $stages[] = [
                    'name' => $this->getStageDisplayName($stage),
                    'stage' => $stage,
                    'completed' => true,
                ];
            }
        }

        return $stages;
    }

    private function getStageDisplayName(string $stage): string
    {
        return match ($stage) {
            'awareness' => 'Descoberta',
            'interest' => 'Interesse',
            'decision' => 'Decisão',
            'action' => 'Ação',
            'advocacy' => 'Defesa',
            default => str($stage)->title(),
        };
    }

    private function determineConversionStatus($events): string
    {
        $eventNames = $events->pluck('event_name')->toArray();

        if (in_array('purchase.completed', $eventNames)) {
            return 'converted';
        }

        if (in_array('cart.abandoned', $eventNames)) {
            return 'abandoned';
        }

        return 'pending';
    }

    public function render()
    {
        return view('livewire.marketplace.customer-journey-timeline');
    }
}
