<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">📊 Google Trends Importer</h1>
        <button wire:click="fetchLatestTrends" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            🔄 Buscar Tendências
        </button>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow p-6 space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Watchlist</label>
                <select wire:model.live="watchlist" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">
                    <option value="">Selecione uma Watchlist</option>
                    @foreach ($watchlists as $w)
                        <option value="{{ $w->id }}">{{ $w->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Categoria</label>
                <select wire:model.live="selectedCategory" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">
                    <option value="">Todas as categorias</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Lista de Trending Topics -->
    <div class="space-y-3">
        @forelse ($trendingTopics as $topic)
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500 hover:shadow-lg transition flex items-center justify-between">
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-gray-900">{{ $topic['topic'] }}</h3>
                    @if ($topic['description'])
                        <p class="text-sm text-gray-600 mt-1">{{ $topic['description'] }}</p>
                    @endif
                    <div class="flex gap-4 mt-2 text-xs text-gray-500">
                        <span>📈 {{ $topic['growth_percentage'] ?? 'N/A' }}% crescimento</span>
                        <span>🔍 {{ number_format($topic['search_volume'] ?? 0) }} buscas</span>
                        <span class="bg-gray-200 px-2 py-1 rounded">{{ $topic['category'] }}</span>
                    </div>
                </div>
                <button
                    wire:click="importToWatchlist({{ $topic['id'] }})"
                    @if (!$watchlist) disabled @endif
                    class="ml-4 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:bg-gray-400"
                >
                    ➕ Importar
                </button>
            </div>
        @empty
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                <p class="text-yellow-800">Nenhum trending topic encontrado. Clique em "Buscar Tendências" para começar.</p>
            </div>
        @endforelse
    </div>

    <!-- Info -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <p class="text-sm text-blue-800">
            💡 <strong>Como funciona:</strong> Busque trending topics do Google Trends, selecione uma Watchlist e importe os tópicos como Trends.
            O sistema automaticamente calculará Trend Score e encontrará produtos que combinam com cada tendência.
        </p>
    </div>
</div>
