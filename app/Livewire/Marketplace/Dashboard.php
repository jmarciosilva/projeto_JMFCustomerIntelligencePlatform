<?php

namespace App\Livewire\Marketplace;

use App\Models\Application;
use App\Models\Event;
use App\Models\MarketplaceMetric;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Dashboard extends Component
{
    public Application $application;

    public string $period = '7'; // dias

    public ?int $selectedSeller = null;

    public array $chartData = [];

    public array $metrics = [];

    public array $sellers = [];

    public array $products = [];

    public function mount(Application $application)
    {
        $this->application = $application;
        $this->loadData();
    }

    public function updatedPeriod()
    {
        $this->loadData();
    }

    public function updatedSelectedSeller()
    {
        $this->loadData();
    }

    private function loadData(): void
    {
        $days = (int) $this->period;
        $startDate = now()->subDays($days)->startOfDay();
        $endDate = now()->endOfDay();

        $query = Event::where('application_id', $this->application->id)
            ->whereBetween('occurred_at', [$startDate, $endDate]);

        if ($this->selectedSeller) {
            $query->whereJsonContains('properties->seller_id', $this->selectedSeller);
        }

        $events = $query->get();

        // Calcular métricas principais
        $this->metrics = [
            'total_events' => $events->count(),
            'product_views' => $events->where('event_name', 'product.viewed')->count(),
            'cart_adds' => $events->where('event_name', 'cart.item_added')->count(),
            'purchases' => $events->where('event_name', 'purchase.completed')->count(),
            'reviews' => $events->where('event_name', 'review.submitted')->count(),
            'revenue' => $events->where('event_name', 'purchase.completed')
                ->sum(fn ($e) => $e->properties['total_value'] ?? 0),
            'unique_visitors' => $events->pluck('visitor_id')->unique()->count(),
            'cart_abandonment_rate' => $this->calculateAbandonmentRate($events),
        ];

        // Dados para gráfico de tendência
        $this->chartData = $this->prepareChartData($events, $startDate, $endDate);

        // Vendedores top
        $this->sellers = $this->getTopSellers($events);

        // Produtos top
        $this->products = $this->getTopProducts($events);
    }

    private function calculateAbandonmentRate($events): float
    {
        $cartAbandoned = $events->where('event_name', 'cart.abandoned')->count();
        $cartViewed = $events->where('event_name', 'cart.viewed')->count();

        return $cartViewed > 0 ? round(($cartAbandoned / $cartViewed) * 100, 2) : 0;
    }

    private function prepareChartData($events, $startDate, $endDate): array
    {
        $dates = [];
        $views = [];
        $purchases = [];

        $current = $startDate->copy();
        while ($current <= $endDate) {
            $dateStr = $current->toDateString();
            $dates[] = $current->format('d/m');

            $dayEvents = $events->filter(fn ($e) => $e->occurred_at->toDateString() === $dateStr);
            $views[] = $dayEvents->where('event_name', 'product.viewed')->count();
            $purchases[] = $dayEvents->where('event_name', 'purchase.completed')->count();

            $current->addDay();
        }

        return [
            'labels' => $dates,
            'views' => $views,
            'purchases' => $purchases,
        ];
    }

    private function getTopSellers($events): array
    {
        return $events->groupBy('properties.seller_id')
            ->map(function ($sellerEvents, $sellerId) {
                return [
                    'seller_id' => $sellerId,
                    'views' => $sellerEvents->where('event_name', 'product.viewed')->count(),
                    'purchases' => $sellerEvents->where('event_name', 'purchase.completed')->count(),
                    'revenue' => $sellerEvents->where('event_name', 'purchase.completed')
                        ->sum(fn ($e) => $e->properties['total_value'] ?? 0),
                ];
            })
            ->sortByDesc('revenue')
            ->take(10)
            ->values()
            ->toArray();
    }

    private function getTopProducts($events): array
    {
        return $events->groupBy('properties.product_id')
            ->map(function ($productEvents, $productId) {
                return [
                    'product_id' => $productId,
                    'views' => $productEvents->where('event_name', 'product.viewed')->count(),
                    'purchases' => $productEvents->where('event_name', 'purchase.completed')->count(),
                    'conversion_rate' => $this->calculateConversionRate($productEvents),
                ];
            })
            ->sortByDesc('views')
            ->take(10)
            ->values()
            ->toArray();
    }

    private function calculateConversionRate($events): float
    {
        $views = $events->where('event_name', 'product.viewed')->count();
        $purchases = $events->where('event_name', 'purchase.completed')->count();

        return $views > 0 ? round(($purchases / $views) * 100, 2) : 0;
    }

    public function render()
    {
        return view('livewire.marketplace.dashboard');
    }
}
