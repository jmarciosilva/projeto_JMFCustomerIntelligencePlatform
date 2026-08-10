<?php

namespace App\Livewire\Admin\Affiliate;

use App\Models\AffiliateProduct;
use App\Models\AffiliateProgram;
use App\Models\Watchlist;
use Livewire\Component;

class ProductSuggestionsByTrends extends Component
{
    public ?int $watchlist = null;
    public array $suggestions = [];
    public array $selectedProducts = [];
    public ?int $selectedProgramId = null;
    public bool $showImportForm = false;

    public function mount(): void
    {
        // Initialize
    }

    public function loadSuggestions(): void
    {
        if (!$this->watchlist) {
            $this->dispatch('toast', message: 'Selecione uma Watchlist primeiro', type: 'error');
            return;
        }

        $this->dispatch('show-loading', message: 'Buscando produtos relacionados aos trends...');

        $watchlistModel = Watchlist::find($this->watchlist);
        if (!$watchlistModel) {
            $this->dispatch('toast', message: 'Watchlist não encontrada', type: 'error');
            return;
        }

        // Get trends from watchlist
        $trends = $watchlistModel->trends()->where('status', 'active')->pluck('term')->toArray();

        if (empty($trends)) {
            $this->dispatch('toast', message: 'Nenhum trend ativo nesta Watchlist', type: 'error');
            return;
        }

        // Generate mock product suggestions based on trends
        $this->suggestions = $this->generateMockSuggestions($trends);

        $this->dispatch('toast', message: count($this->suggestions) . ' produtos encontrados!', type: 'success');
    }

    private function generateMockSuggestions(array $trends): array
    {
        $suggestions = [];
        $productDatabase = [
            'moda' => [
                ['name' => 'Jaqueta de Inverno Premium', 'category' => 'Moda', 'price' => 189.90],
                ['name' => 'Suéter Gola Alta', 'category' => 'Moda', 'price' => 129.90],
                ['name' => 'Calça Jeans Inverno', 'category' => 'Moda', 'price' => 99.90],
                ['name' => 'Bota de Couro', 'category' => 'Moda', 'price' => 249.90],
                ['name' => 'Casaco Long', 'category' => 'Moda', 'price' => 199.90],
                ['name' => 'Luva de Lã', 'category' => 'Moda', 'price' => 39.90],
                ['name' => 'Gorro Inverno', 'category' => 'Moda', 'price' => 49.90],
                ['name' => 'Lenço de Seda', 'category' => 'Moda', 'price' => 59.90],
            ],
            'tecnologia' => [
                ['name' => 'Fone Bluetooth Wireless', 'category' => 'Tecnologia', 'price' => 199.90],
                ['name' => 'Smartwatch Premium', 'category' => 'Tecnologia', 'price' => 899.90],
                ['name' => 'Carregador Rápido USB-C', 'category' => 'Tecnologia', 'price' => 89.90],
                ['name' => 'Powerbank 20000mAh', 'category' => 'Tecnologia', 'price' => 129.90],
            ],
            'casa' => [
                ['name' => 'Luminária LED', 'category' => 'Casa', 'price' => 79.90],
                ['name' => 'Travesseiro Ergonômico', 'category' => 'Casa', 'price' => 169.90],
                ['name' => 'Cortina Blackout', 'category' => 'Casa', 'price' => 199.90],
                ['name' => 'Tapete Persa', 'category' => 'Casa', 'price' => 299.90],
            ],
            'beleza' => [
                ['name' => 'Sérum Facial Premium', 'category' => 'Beleza', 'price' => 119.90],
                ['name' => 'Creme Noturno', 'category' => 'Beleza', 'price' => 89.90],
                ['name' => 'Máscara Facial', 'category' => 'Beleza', 'price' => 69.90],
            ],
        ];

        foreach ($trends as $trend) {
            $lowerTrend = strtolower($trend);

            foreach ($productDatabase as $category => $products) {
                if (str_contains($lowerTrend, $category) || str_contains($category, $lowerTrend)) {
                    foreach ($products as $product) {
                        if (!collect($suggestions)->contains('name', $product['name'])) {
                            $suggestions[] = array_merge($product, [
                                'id' => md5($product['name']),
                                'trend' => $trend,
                            ]);
                        }
                    }
                }
            }
        }

        // If no exact match, return general suggestions based on categories
        if (empty($suggestions)) {
            foreach ($productDatabase as $category => $products) {
                $suggestions = array_merge($suggestions, array_map(fn($p) => array_merge($p, ['id' => md5($p['name']), 'trend' => 'Geral']), $products));
            }
        }

        return array_slice($suggestions, 0, 12);
    }

    public function toggleProduct(string $productId): void
    {
        if (in_array($productId, $this->selectedProducts)) {
            $this->selectedProducts = array_filter($this->selectedProducts, fn($id) => $id !== $productId);
        } else {
            $this->selectedProducts[] = $productId;
        }
    }

    public function importSelected(): void
    {
        if (empty($this->selectedProducts)) {
            $this->dispatch('toast', message: 'Selecione pelo menos um produto', type: 'error');
            return;
        }

        if (!$this->selectedProgramId) {
            $this->dispatch('toast', message: 'Selecione um programa de afiliado', type: 'error');
            return;
        }

        $program = AffiliateProgram::find($this->selectedProgramId);
        $count = 0;
        
        foreach ($this->selectedProducts as $productId) {
            $product = collect($this->suggestions)->firstWhere('id', $productId);
            if ($product) {
                AffiliateProduct::firstOrCreate(
                    ['name' => $product['name']],
                    [
                        'affiliate_program_id' => $this->selectedProgramId,
                        'category' => $product['category'],
                        'affiliate_url' => $program->website ?? 'https://www.influenciadormagalu.com.br/taemalta',
                        'status' => 'active',
                    ]
                );
                $count++;
            }
        }

        $this->selectedProducts = [];
        $this->selectedProgramId = null;
        $this->showImportForm = false;
        $this->dispatch('toast', message: "{$count} produtos importados com sucesso!", type: 'success');
    }

    public function render()
    {
        $app = auth()->user()->application ?? Application::first();

        $watchlists = Watchlist::where('application_id', $app->id)
            ->orderByDesc('created_at')
            ->get();

        $programs = AffiliateProgram::where('application_id', $app->id)
            ->where('status', 'active')
            ->get();

        return view('livewire.admin.affiliate.product-suggestions-by-trends', [
            'watchlists' => $watchlists,
            'programs' => $programs,
        ])->layout('layouts.admin', ['header' => '🛍️ Sugestões de Produtos por Trends']);
    }
}
