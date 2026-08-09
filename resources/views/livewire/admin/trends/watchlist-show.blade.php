<div>
    <x-slot:header>{{ $watchlist->name }}</x-slot:header>

    <x-slot:help>
        <x-help-modal title="Ajuda — Tendências da Watchlist">
            <p>Cada linha é uma <strong>Tendência</strong> (palavra-chave ou hashtag) monitorada individualmente. Clique em "Coletar agora" para buscar sinais imediatamente (dados próprios da plataforma) ou abra a tendência para ver o histórico completo e registrar uma observação manual.</p>
        </x-help-modal>
    </x-slot:help>

    <div class="mb-6 flex items-center justify-between">
        <p class="text-sm text-slate-400">{{ $watchlist->description }}</p>
        <a href="{{ route('admin.trends.watchlists.index') }}" class="text-sm text-slate-400 hover:text-white">← Voltar</a>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-800">
        <table class="w-full text-sm">
            <thead class="bg-slate-900">
                <tr class="text-left text-xs font-medium uppercase tracking-wide text-slate-500">
                    <th class="px-4 py-3">Termo</th>
                    <th class="px-4 py-3">Tipo</th>
                    <th class="px-4 py-3">Trend Score</th>
                    <th class="px-4 py-3">Snapshots</th>
                    <th class="px-4 py-3">Última coleta</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse ($trends as $trend)
                    <tr class="bg-slate-900/60">
                        <td class="px-4 py-3 text-slate-200">
                            <a href="{{ route('admin.trends.show', $trend) }}" class="hover:text-amber-400">{{ $trend->term }}</a>
                        </td>
                        <td class="px-4 py-3 text-slate-400">{{ $trend->type }}</td>
                        <td class="px-4 py-3"><x-trend-score-badge :score="$trend->trend_score" /></td>
                        <td class="px-4 py-3 text-slate-400">{{ $trend->snapshots_count }}</td>
                        <td class="px-4 py-3 text-slate-400">{{ $trend->last_collected_at?->diffForHumans() ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1.5 text-xs">
                                <span class="h-2 w-2 rounded-full {{ $trend->isActive() ? 'bg-emerald-400' : 'bg-rose-500' }}"></span>
                                {{ $trend->isActive() ? 'Ativa' : 'Inativa' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            @can('update', $trend)
                                <button type="button" wire:click="collectNow({{ $trend->id }})"
                                        class="text-amber-400 hover:text-amber-300">
                                    Coletar agora
                                </button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr class="bg-slate-900/60">
                        <td class="px-4 py-6 text-center text-slate-500" colspan="7">Nenhuma tendência cadastrada — edite a watchlist para adicionar palavras-chave/hashtags.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
