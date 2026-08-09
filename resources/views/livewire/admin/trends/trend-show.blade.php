<div>
    <x-slot:header>{{ $trend->term }}</x-slot:header>

    <x-slot:help>
        <x-help-modal title="Ajuda — Detalhe da Tendência">
            <p>Histórico de coleta desta tendência. "Coletar agora" busca sinais automaticamente nas fontes já configuradas (dados próprios da plataforma) sem esperar o agendamento diário.</p>
            <p>Quando uma fonte não tem coleta automática disponível (ex.: observação feita manualmente no Instagram), registre-a no formulário abaixo — vira um snapshot com origem "manual".</p>
            <p><strong>Trend Score</strong> (0-100) mede apenas nível de interesse/tendência — não é oportunidade comercial (isso vem na Fase 26, Product Opportunity Engine). "Recalcular score" reprocessa a partir dos snapshots já coletados, sem esperar o agendamento diário.</p>
        </x-help-modal>
    </x-slot:help>

    <div class="mb-6 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3 text-sm text-slate-400">
            <span>Watchlist: <a href="{{ route('admin.trends.watchlists.show', $trend->watchlist) }}" class="text-amber-400 hover:text-amber-300">{{ $trend->watchlist->name }}</a></span>
            <span>·</span>
            <span>Tipo: {{ $trend->type }}</span>
            <span>·</span>
            <span class="flex items-center gap-1.5">Trend Score: <x-trend-score-badge :score="$trend->trend_score" /></span>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" wire:click="recalculateScore" wire:loading.attr="disabled"
                    class="rounded-lg border border-amber-400 px-4 py-2 text-sm font-semibold text-amber-400 hover:bg-amber-400/10">
                Recalcular score
            </button>
            <button type="button" wire:click="collectNow" wire:loading.attr="disabled"
                    class="rounded-lg bg-amber-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-amber-300">
                Coletar agora
            </button>
        </div>
    </div>

    @if ($trend->trend_score_breakdown)
        <div class="mb-6 rounded-xl border border-slate-800 bg-slate-900 p-4">
            <p class="mb-2 text-xs font-medium uppercase tracking-wide text-slate-500">
                Fatores considerados (janela de {{ $trend->trend_score_breakdown['window_size'] }} snapshots, calculado {{ $trend->trend_score_computed_at?->diffForHumans() }})
            </p>
            <div class="flex flex-wrap gap-4 text-sm">
                @foreach ($trend->trend_score_breakdown['factors'] as $factor => $value)
                    <div>
                        <span class="text-slate-500">{{ match ($factor) {
                            'growth' => 'Crescimento',
                            'volume' => 'Volume',
                            'recurrence' => 'Recorrência',
                            'stability' => 'Estabilidade',
                            'engagement' => 'Engajamento',
                            default => $factor,
                        } }}:</span>
                        <span class="text-slate-200 font-medium">{{ number_format($value, 0) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="mb-4 flex items-center gap-2">
        @foreach ([7 => '7 dias', 30 => '30 dias', 90 => '90 dias', 365 => '1 ano'] as $days => $label)
            <button type="button" wire:click="$set('period', {{ $days }})"
                    class="rounded-lg px-3 py-1.5 text-xs font-medium {{ $period === $days ? 'bg-amber-400 text-slate-950' : 'bg-slate-900 text-slate-400 hover:text-white' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    <div class="mb-6 rounded-xl border border-slate-800 bg-slate-900 p-4">
        <canvas id="trendChart" height="80"></canvas>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-800 mb-6">
        <table class="w-full text-sm">
            <thead class="bg-slate-900">
                <tr class="text-left text-xs font-medium uppercase tracking-wide text-slate-500">
                    <th class="px-4 py-3">Coletado em</th>
                    <th class="px-4 py-3">Origem</th>
                    <th class="px-4 py-3">Menções</th>
                    <th class="px-4 py-3">Engajamento</th>
                    <th class="px-4 py-3">Velocidade</th>
                    <th class="px-4 py-3">Score</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse ($snapshots->sortByDesc('collected_at') as $snapshot)
                    <tr class="bg-slate-900/60">
                        <td class="px-4 py-3 text-slate-200">{{ $snapshot->collected_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 text-slate-400 font-mono text-xs">{{ $snapshot->source }}</td>
                        <td class="px-4 py-3 text-slate-400">{{ $snapshot->mentions ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-400">{{ $snapshot->engagement ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-400">{{ $snapshot->velocity !== null ? $snapshot->velocity.'%' : '—' }}</td>
                        <td class="px-4 py-3 text-slate-400">{{ $snapshot->score ?? '—' }}</td>
                    </tr>
                @empty
                    <tr class="bg-slate-900/60">
                        <td class="px-4 py-6 text-center text-slate-500" colspan="6">Nenhum snapshot no período selecionado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @can('update', $trend)
        <div class="max-w-lg rounded-xl border border-slate-800 bg-slate-900 p-6">
            <h3 class="mb-4 text-sm font-semibold text-slate-200">Registrar observação manual</h3>
            <form wire:submit="registerManualSnapshot" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="mentions" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Menções</label>
                        <input wire:model="mentions" type="text" id="mentions"
                               class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                        @error('mentions')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="engagement" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Engajamento</label>
                        <input wire:model="engagement" type="text" id="engagement"
                               class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                    </div>
                    <div>
                        <label for="velocity" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Velocidade (%)</label>
                        <input wire:model="velocity" type="text" id="velocity"
                               class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                    </div>
                    <div>
                        <label for="score" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Score (0-100)</label>
                        <input wire:model="score" type="text" id="score"
                               class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                        @error('score')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div>
                    <label for="notes" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Observações</label>
                    <textarea wire:model="notes" id="notes" rows="2"
                              class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400"></textarea>
                </div>
                <button type="submit" wire:loading.attr="disabled"
                        class="rounded-lg bg-amber-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-amber-300">
                    Registrar
                </button>
            </form>
        </div>
    @endcan

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            new Chart(document.getElementById('trendChart'), {
                type: 'line',
                data: {
                    labels: @json($chartData['labels']),
                    datasets: [{
                        label: 'Menções',
                        data: @json($chartData['mentions']),
                        borderColor: '#fbbf24',
                        backgroundColor: 'rgba(251, 191, 36, 0.1)',
                        tension: 0.3,
                    }],
                },
                options: {
                    plugins: { legend: { labels: { color: '#94a3b8' } } },
                    scales: {
                        x: { ticks: { color: '#94a3b8' }, grid: { color: '#1e293b' } },
                        y: { ticks: { color: '#94a3b8' }, grid: { color: '#1e293b' } },
                    },
                },
            });
        });
    </script>
</div>
