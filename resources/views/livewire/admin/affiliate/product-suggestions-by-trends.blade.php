<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-white">🛍️ Sugestões de Produtos por Trends</h1>
    </div>

    <!-- Explicação -->
    <div class="bg-gradient-to-r from-amber-600/20 to-amber-500/10 border border-amber-600/30 rounded-lg p-6">
        <h2 class="text-lg font-bold text-amber-300 mb-2">📚 Como Funciona</h2>
        <p class="text-slate-300 mb-3">
            1. Selecione uma Watchlist com seus trends<br>
            2. Clique em "Buscar Produtos"<br>
            3. O sistema sugere produtos relacionados aos trends<br>
            4. Selecione os produtos que quer vender<br>
            5. Importe para seu programa de afiliado
        </p>
    </div>

    <!-- Seleção de Watchlist -->
    <div class="bg-slate-900 border border-slate-800 rounded-lg p-6 space-y-4">
        <div class="space-y-2">
            <label class="block text-sm font-bold text-slate-300">Selecione uma Watchlist</label>
            <select
                wire:model.live="watchlist"
                class="w-full px-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:border-amber-400 focus:outline-none"
            >
                <option value="">-- Escolha uma Watchlist --</option>
                @forelse ($watchlists as $w)
                    <option value="{{ $w->id }}">
                        {{ $w->name }} ({{ $w->trends()->count() }} trends)
                    </option>
                @empty
                    <option disabled>Nenhuma Watchlist encontrada</option>
                @endforelse
            </select>
            @if ($watchlist)
                @php
                    $selectedWatchlist = \App\Models\Watchlist::find($watchlist);
                @endphp
                @if ($selectedWatchlist)
                    <div class="mt-2 text-sm text-slate-300">
                        <p class="font-semibold">📌 Trends nesta Watchlist:</p>
                        <div class="flex flex-wrap gap-2 mt-1">
                            @forelse ($selectedWatchlist->trends()->where('status', 'active')->get() as $trend)
                                <span class="bg-amber-600/20 border border-amber-600/50 px-3 py-1 rounded-full text-amber-300 text-xs">
                                    #{{ $trend->term }}
                                </span>
                            @empty
                                <span class="text-slate-400 text-xs">Nenhum trend nesta Watchlist ainda</span>
                            @endforelse
                        </div>
                    </div>
                @endif
            @endif
        </div>

        <button
            wire:click="loadSuggestions"
            @disabled(!$watchlist)
            class="w-full px-6 py-3 bg-amber-600 text-white rounded-lg hover:bg-amber-700 disabled:bg-slate-700 disabled:text-slate-400 transition font-bold text-lg"
        >
            🔍 Buscar Produtos Relacionados
        </button>
    </div>

    <!-- Lista de Sugestões -->
    @if (!empty($suggestions))
        <div class="space-y-4">
            <h2 class="text-2xl font-bold text-white">
                💡 {{ count($suggestions) }} Produtos Encontrados
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($suggestions as $product)
                    <div
                        class="bg-slate-900 border-2 rounded-lg p-4 cursor-pointer transition {{ in_array($product['id'], $selectedProducts) ? 'border-amber-600 bg-amber-600/10' : 'border-slate-800 hover:border-slate-700' }}"
                    >
                        <div class="flex items-start gap-3">
                            <input
                                type="checkbox"
                                wire:click="toggleProduct('{{ $product['id'] }}')"
                                {{ in_array($product['id'], $selectedProducts) ? 'checked' : '' }}
                                class="mt-1 w-5 h-5 rounded border-slate-600 bg-slate-800 text-amber-600 focus:ring-amber-500 cursor-pointer"
                            >
                            <div class="flex-1">
                                <h3 class="font-bold text-white text-sm">{{ $product['name'] }}</h3>

                                <div class="mt-2 space-y-1">
                                    <p class="text-xs text-slate-400">
                                        <span class="bg-slate-800 px-2 py-1 rounded">{{ $product['category'] }}</span>
                                    </p>
                                    <p class="text-sm text-amber-400 font-semibold">R$ {{ number_format($product['price'], 2, ',', '.') }}</p>
                                    <p class="text-xs text-slate-500">Trend: <strong>#{{ $product['trend'] }}</strong></p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Botão de Importação -->
            <div>
                @if (count($selectedProducts) > 0)
                    <div class="bg-slate-900 border-2 border-amber-600 rounded-lg p-6 space-y-4">
                        <h2 class="text-lg font-bold text-white">
                            📦 {{ count($selectedProducts) }} Produto(s) Selecionado(s)
                        </h2>

                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-300">Importar para qual programa?</label>
                        <select
                            wire:model.live="selectedProgramId"
                            class="w-full px-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:border-amber-400 focus:outline-none"
                        >
                            <option value="">-- Selecione um programa --</option>
                            @forelse ($programs as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @empty
                                <option disabled>Nenhum programa encontrado</option>
                            @endforelse
                        </select>
                        @if (empty($programs))
                            <p class="text-xs text-amber-400">⚠️ Nenhum programa de afiliado ativo. Crie um em Setup → Programas</p>
                        @endif
                    </div>

                    <button
                        wire:click="importSelected"
                        type="button"
                        {{ (int)$selectedProgramId > 0 ? '' : 'disabled' }}
                        class="w-full px-6 py-3 {{ (int)$selectedProgramId > 0 ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-slate-700 cursor-not-allowed' }} text-white rounded-lg transition font-bold"
                    >
                        ✅ Importar Produtos
                        @if ((int)$selectedProgramId > 0)
                            para {{ $programs->find($selectedProgramId)?->name }}
                        @else
                            (selecione um programa)
                        @endif
                    </button>
                    </div>
                @else
                    <div class="bg-slate-900 border-2 border-slate-700 rounded-lg p-6 text-center opacity-50">
                        <p class="text-slate-400">Selecione produtos acima para importar</p>
                    </div>
                @endif
            </div>
        </div>
    @elseif (count($suggestions) === 0 && $watchlist)
        <div class="bg-slate-900 border border-slate-800 rounded-lg p-8 text-center">
            <p class="text-slate-300 text-lg">Nenhum produto encontrado para estes trends</p>
            <p class="text-slate-400 text-sm mt-2">Tente com diferentes keywords ou categorias</p>
        </div>
    @endif

    <!-- Próximos Passos -->
    <div class="bg-amber-600/10 border border-amber-600/30 rounded-lg p-6">
        <h2 class="text-lg font-bold text-amber-300 mb-4">📋 Próximos Passos</h2>
        <div class="space-y-3 text-slate-300 text-sm">
            <p>
                ✅ <strong>Produtos Importados?</strong> Vá em <a href="{{ route('admin.affiliate.products.index') }}" class="text-amber-400 hover:underline">Setup → 📦 Produtos</a> para visualizar.
            </p>
            <p>
                📋 <strong>Criar Campanha?</strong> Vá em <a href="{{ route('admin.affiliate.campaigns.index') }}" class="text-amber-400 hover:underline">Campanhas & Conteúdo → 📋 Campanhas</a>.
            </p>
            <p>
                🔗 <strong>Gerar Links?</strong> Vá em <a href="{{ route('admin.affiliate.links.index') }}" class="text-amber-400 hover:underline">Campanhas & Conteúdo → 🔗 Links</a>.
            </p>
            <p>
                📊 <strong>Ver Recomendações?</strong> Vá em <a href="{{ route('admin.affiliate.recommendations.index') }}" class="text-amber-400 hover:underline">Performance & Conversões → 🎯 Recomendações</a>.
            </p>
        </div>
    </div>
</div>
