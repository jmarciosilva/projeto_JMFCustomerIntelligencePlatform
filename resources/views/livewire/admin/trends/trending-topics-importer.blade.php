<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-white">📊 Google Trends</h1>
        <button wire:click="fetchLatestTrends" class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition font-medium">
            🔄 Buscar Tendências
        </button>
    </div>

    <!-- Instruções para Novos Usuários -->
    @if (count($watchlists) === 0)
        <div class="bg-amber-600/10 border-l-4 border-amber-600 rounded-lg p-6">
            <h2 class="text-lg font-bold text-amber-300 mb-2">🚀 Primeiro Acesso?</h2>
            <p class="text-slate-300 mb-3">
                Veja quais produtos estão sendo mais procurados agora. Selecione os trends que te interessam e crie uma <strong>Watchlist</strong> para começar a monitorar!
            </p>
            <p class="text-slate-400 text-sm">Exemplo: "Tecnologia em alta", "Moda Sustentável", etc.</p>
        </div>
    @endif

    <!-- Filtros -->
    <div class="bg-slate-900 rounded-lg shadow-lg border border-slate-800 p-6 space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">Watchlist (Opcional)</label>
                <select wire:model.live="watchlist" class="w-full rounded-lg bg-slate-800 border border-slate-700 text-white focus:border-amber-400 focus:ring-amber-400">
                    <option value="">Criar nova Watchlist com selecionados</option>
                    @foreach ($watchlists as $w)
                        <option value="{{ $w->id }}">{{ $w->name }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-slate-400 mt-1">Selecione uma Watchlist existente OU deixe em branco para criar uma nova</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">Categoria</label>
                <select wire:model.live="selectedCategory" class="w-full rounded-lg bg-slate-800 border border-slate-700 text-white focus:border-amber-400 focus:ring-amber-400">
                    <option value="">Todas as categorias</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Ações com Selecionados -->
        @if (!empty($selectedTrends))
            <div class="pt-4 border-t border-slate-700 flex items-center justify-between bg-amber-600/10 p-4 rounded-lg">
                <span class="text-amber-300 font-semibold">{{ count($selectedTrends) }} trend(s) selecionado(s)</span>
                @if ($watchlist)
                    <button
                        wire:click="importMultiple(@js($selectedTrends))"
                        class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition font-medium"
                    >
                        ✅ Importar para {{ $watchlist->name ?? 'Watchlist' }}
                    </button>
                @else
                    <button
                        wire:click="createWatchlistWithSelected"
                        class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition font-medium"
                    >
                        📝 Criar Nova Watchlist
                    </button>
                @endif
            </div>
        @endif
    </div>

    <!-- Formulário de Nova Watchlist -->
    @if ($showNewWatchlistForm)
        <div class="bg-slate-900 border-2 border-amber-600 rounded-lg p-6 space-y-4">
            <h2 class="text-lg font-bold text-white">📝 Nome da Nova Watchlist</h2>
            <div class="space-y-2">
                <input
                    type="text"
                    wire:model="newWatchlistName"
                    placeholder="Ex: Tecnologia em Alta, Moda 2024, etc"
                    class="w-full px-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white placeholder-slate-500 focus:border-amber-400 focus:outline-none"
                    autofocus
                >
                <p class="text-xs text-slate-400">{{ count($selectedTrends) }} trend(s) serão importados para esta Watchlist</p>
            </div>
            <div class="flex gap-3">
                <button
                    wire:click="saveNewWatchlist"
                    class="px-6 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition font-medium"
                >
                    ✅ Criar Watchlist
                </button>
                <button
                    wire:click="$set('showNewWatchlistForm', false)"
                    class="px-6 py-2 border border-slate-700 text-slate-300 rounded-lg hover:bg-slate-800 transition font-medium"
                >
                    ✕ Cancelar
                </button>
            </div>
        </div>
    @endif

    <!-- Lista de Trending Topics -->
    <div class="space-y-3">
        @forelse ($trendingTopics as $topic)
            <div class="bg-slate-900 rounded-lg shadow-lg p-4 border-l-4 border-amber-500 hover:shadow-xl transition flex items-center gap-4">
                <!-- Checkbox -->
                <div class="flex-shrink-0">
                    <input
                        type="checkbox"
                        wire:model.live="selectedTrends"
                        value="{{ $topic['id'] }}"
                        @change="$wire.toggleTrend({{ $topic['id'] }})"
                        class="w-5 h-5 rounded border-slate-600 bg-slate-800 text-amber-600 focus:ring-amber-500 cursor-pointer"
                    >
                </div>

                <!-- Conteúdo -->
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

                <!-- Ação Individual -->
                @if ($watchlist)
                    <button
                        wire:click="importToWatchlist({{ $topic['id'] }})"
                        class="ml-4 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition font-medium whitespace-nowrap"
                    >
                        ➕ Importar
                    </button>
                @endif
            </div>
        @empty
            <div class="bg-slate-900 border border-slate-800 rounded-lg p-6 text-center">
                <p class="text-slate-300 text-lg">🔍 Nenhum trending topic encontrado</p>
                <p class="text-slate-400 text-sm mt-2">Clique em "Buscar Tendências" para começar a explorar trends do Google.</p>
            </div>
        @endforelse
    </div>

    <!-- Info -->
    <div class="bg-slate-900/50 border border-slate-800 rounded-lg p-4">
        <p class="text-sm text-slate-300">
            💡 <strong>Como funciona:</strong> Selecione trends que te interessam → Crie uma Watchlist → O sistema encontrará automaticamente produtos que combinam com essas tendências.
        </p>
    </div>
</div>
