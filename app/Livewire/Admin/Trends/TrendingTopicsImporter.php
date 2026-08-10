<?php

namespace App\Livewire\Admin\Trends;

use App\Domain\Trends\FetchGoogleTrendsAction;
use App\Models\Application;
use App\Models\Trend;
use App\Models\TrendingTopic;
use App\Models\Watchlist;
use Livewire\Component;

class TrendingTopicsImporter extends Component
{
    public array $trendingTopics = [];
    public ?Watchlist $watchlist = null;
    public string $region = 'BR';
    public ?string $selectedCategory = null;
    public array $selectedTrends = [];
    public string $newWatchlistName = '';
    public bool $showNewWatchlistForm = false;

    #[On('trendingTopicsUpdated')]
    public function mount(): void
    {
        $this->loadTrendingTopics();
    }

    public function loadTrendingTopics(): void
    {
        $query = TrendingTopic::where('region', $this->region)
            ->orderByDesc('fetched_at')
            ->orderByDesc('growth_percentage');

        if ($this->selectedCategory) {
            $query->where('category', $this->selectedCategory);
        }

        $this->trendingTopics = $query->get()->toArray();
    }

    public function fetchLatestTrends(): void
    {
        $this->dispatch('show-loading', message: 'Buscando trending topics do Google Trends...');

        $action = new FetchGoogleTrendsAction($this->region);
        $action->execute();

        $this->loadTrendingTopics();
        $this->dispatch('toast', message: count($this->trendingTopics) . ' trending topics capturados!', type: 'success');
    }

    public function toggleTrend(int $trendingTopicId): void
    {
        if (in_array($trendingTopicId, $this->selectedTrends)) {
            $this->selectedTrends = array_filter($this->selectedTrends, fn($id) => $id !== $trendingTopicId);
        } else {
            $this->selectedTrends[] = $trendingTopicId;
        }
    }

    public function createWatchlistWithSelected(): void
    {
        if (empty($this->selectedTrends)) {
            $this->dispatch('toast', message: 'Selecione pelo menos um trend', type: 'error');
            return;
        }

        $this->showNewWatchlistForm = true;
    }

    public function saveNewWatchlist(): void
    {
        if (empty($this->newWatchlistName)) {
            $this->dispatch('toast', message: 'Digite um nome para a Watchlist', type: 'error');
            return;
        }

        $app = auth()->user()->application ?? Application::first();

        $watchlist = Watchlist::create([
            'application_id' => $app->id,
            'name' => $this->newWatchlistName,
            'description' => 'Criada a partir de trending topics',
            'status' => 'active',
        ]);

        $count = 0;
        foreach ($this->selectedTrends as $trendingTopicId) {
            $topic = TrendingTopic::find($trendingTopicId);
            if ($topic) {
                Trend::firstOrCreate(
                    ['watchlist_id' => $watchlist->id, 'term' => $topic->topic],
                    ['description' => $topic->description, 'status' => 'active']
                );
                $count++;
            }
        }

        $this->selectedTrends = [];
        $this->newWatchlistName = '';
        $this->showNewWatchlistForm = false;
        $this->dispatch('toast', message: "Watchlist '{$watchlist->name}' criada com {$count} trends!", type: 'success');
    }

    public function importToWatchlist(int $trendingTopicId): void
    {
        if (!$this->watchlist) {
            $this->dispatch('toast', message: 'Selecione uma Watchlist primeiro', type: 'error');
            return;
        }

        $topic = TrendingTopic::find($trendingTopicId);
        if (!$topic) {
            return;
        }

        Trend::firstOrCreate(
            ['watchlist_id' => $this->watchlist->id, 'term' => $topic->topic],
            [
                'description' => $topic->description,
                'status' => 'active',
            ]
        );

        $this->dispatch('toast', message: "'{$topic->topic}' importado para a Watchlist", type: 'success');
    }

    public function importMultiple(array $ids): void
    {
        if (!$this->watchlist) {
            $this->dispatch('toast', message: 'Selecione uma Watchlist primeiro', type: 'error');
            return;
        }

        $count = 0;
        foreach ($ids as $id) {
            $topic = TrendingTopic::find($id);
            if ($topic) {
                Trend::firstOrCreate(
                    ['watchlist_id' => $this->watchlist->id, 'term' => $topic->topic],
                    ['description' => $topic->description, 'status' => 'active']
                );
                $count++;
            }
        }

        $this->dispatch('toast', message: "{$count} trending topics importados", type: 'success');
    }

    public function render()
    {
        return view('livewire.admin.trends.trending-topics-importer', [
            'watchlists' => Watchlist::where('application_id', auth()->user()->application_id)->get(),
            'categories' => ['Fashion', 'Technology', 'Business', 'Entertainment', 'Sports', 'Health', 'Food', 'Travel', 'Other'],
        ])->layout('layouts.admin', ['header' => '📊 Google Trends Importer']);
    }
}
