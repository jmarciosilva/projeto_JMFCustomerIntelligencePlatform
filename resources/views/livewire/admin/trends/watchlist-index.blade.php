<div>
    <x-slot:header>Watchlists</x-slot:header>

    <x-slot:help>
        <x-help-modal title="Ajuda — Watchlists">
            <p>Uma <strong>Watchlist</strong> agrupa palavras-chave, hashtags e categorias relacionadas (ex.: "Casa": cafeteira, air fryer, aspirador).</p>
            <p>Cada termo cadastrado gera uma <strong>Tendência</strong> monitorada individualmente, com histórico de coleta (Trend Snapshots).</p>
        </x-help-modal>
    </x-slot:help>

    @error('watchlist')
        <div class="mb-4 rounded-lg border border-rose-800 bg-rose-950/50 px-3 py-2 text-sm text-rose-400">
            {{ $message }}
        </div>
    @enderror

    <div class="flex flex-wrap items-center justify-between mb-6 gap-4">
        <div class="flex flex-wrap items-center gap-3">
            <select wire:model.live="applicationId"
                    class="rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                @foreach ($applications as $application)
                    <option value="{{ $application->id }}">{{ $application->name }}</option>
                @endforeach
            </select>

            <input wire:model.live.debounce.300ms="search" type="search" placeholder="Buscar por nome..."
                   class="w-full max-w-sm rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
        </div>

        @can('create', App\Models\Watchlist::class)
            <a href="{{ route('admin.trends.watchlists.create') }}"
               class="shrink-0 rounded-lg bg-amber-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-amber-300">
                Nova watchlist
            </a>
        @endcan
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-800">
        <table class="w-full text-sm">
            <thead class="bg-slate-900">
                <tr class="text-left text-xs font-medium uppercase tracking-wide text-slate-500">
                    <th class="px-4 py-3">Nome</th>
                    <th class="px-4 py-3">Frequência</th>
                    <th class="px-4 py-3">Tendências</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse ($watchlists as $watchlist)
                    <tr class="bg-slate-900/60">
                        <td class="px-4 py-3 text-slate-200">
                            <a href="{{ route('admin.trends.watchlists.show', $watchlist) }}" class="hover:text-amber-400">
                                {{ $watchlist->name }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-slate-400">{{ $watchlist->collection_frequency }}</td>
                        <td class="px-4 py-3 text-slate-400">{{ $watchlist->trends_count }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1.5 text-xs">
                                <span class="h-2 w-2 rounded-full {{ $watchlist->isActive() ? 'bg-emerald-400' : 'bg-rose-500' }}"></span>
                                {{ $watchlist->isActive() ? 'Ativa' : 'Inativa' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right space-x-3 whitespace-nowrap">
                            @can('update', $watchlist)
                                <a href="{{ route('admin.trends.watchlists.edit', $watchlist) }}" class="text-amber-400 hover:text-amber-300">Editar</a>
                            @endcan
                            @can('delete', $watchlist)
                                <button type="button" wire:click="delete({{ $watchlist->id }})"
                                        wire:confirm="Tem certeza que deseja excluir esta watchlist?"
                                        class="text-rose-400 hover:text-rose-300">
                                    Excluir
                                </button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr class="bg-slate-900/60">
                        <td class="px-4 py-6 text-center text-slate-500" colspan="5">Nenhuma watchlist encontrada.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $watchlists->links() }}
    </div>
</div>
