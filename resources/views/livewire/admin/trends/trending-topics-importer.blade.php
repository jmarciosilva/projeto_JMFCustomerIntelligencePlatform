<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-white">📊 Google Trends Importer</h1>
        <button wire:click="fetchLatestTrends" class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition font-medium">
            🔄 Buscar Tendências
        </button>
    </div>

    <!-- Filtros -->
    <div class="bg-slate-900 rounded-lg shadow-lg border border-slate-800 p-6 space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-300">Watchlist</label>
                <select wire:model.live="watchlist" class="mt-1 block w-full rounded-lg bg-slate-800 border border-slate-700 text-white focus:border-amber-400 focus:ring-amber-400">
                    <option value="">Selecione uma Watchlist</option>
                    @foreach ($watchlists as $w)
                        <option value="{{ $w->id }}">{{ $w->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-300">Categoria</label>
                <select wire:model.live="selectedCategory" class="mt-1 block w-full rounded-lg bg-slate-800 border border-slate-700 text-white focus:border-amber-400 focus:ring-amber-400">
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
            <div class="bg-slate-900 rounded-lg shadow-lg p-4 border-l-4 border-amber-500 hover:shadow-xl transition">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-white">{{ $topic['topic'] }}</h3>
                        @if ($topic['description'])
                            <p class="text-sm text-slate-400 mt-1">{{ $topic['description'] }}</p>
                        @endif
                        <div class="flex gap-4 mt-2 text-xs text-slate-400">
                            <span>📈 {{ $topic['growth_percentage'] ?? 'N/A' }}% crescimento</span>
                            <span>🔍 {{ number_format($topic['search_volume'] ?? 0) }} buscas</span>
                            <span class="bg-slate-800 px-2 py-1 rounded">{{ $topic['category'] }}</span>
                        </div>
                    </div>
                    <button
                        wire:click="importToWatchlist({{ $topic['id'] }})"
                        @if (!$watchlist) disabled @endif
                        class="ml-4 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:bg-slate-700 transition font-medium"
                    >
                        ➕ Importar
                    </button>
                </div>
            </div>
        @empty
            <div class="bg-slate-900 border border-slate-800 rounded-lg p-6">
                <p class="text-slate-300">Nenhum trending topic encontrado. Clique em "Buscar Tendências" para começar.</p>
            </div>
        @endforelse
    </div>

    <!-- Info -->
    <div class="bg-slate-900/50 border border-slate-800 rounded-lg p-4">
        <p class="text-sm text-slate-300">
            💡 <strong>Como funciona:</strong> Busque trending topics do Google Trends, selecione uma Watchlist e importe os tópicos como Trends.
            O sistema automaticamente calculará Trend Score e encontrará produtos que combinam com cada tendência.
        </p>
    </div>
</div>
