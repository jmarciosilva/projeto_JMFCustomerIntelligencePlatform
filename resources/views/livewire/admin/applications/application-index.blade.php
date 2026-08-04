<div>
    <x-slot:header>Aplicações</x-slot:header>

    <div class="flex items-center justify-between mb-6 gap-4">
        <input wire:model.live.debounce.300ms="search" type="search" placeholder="Buscar por nome..."
               class="w-full max-w-sm rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">

        @can('create', App\Models\Application::class)
            <a href="{{ route('admin.applications.create') }}"
               class="shrink-0 rounded-lg bg-amber-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-amber-300">
                Nova aplicação
            </a>
        @endcan
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-800">
        <table class="w-full text-sm">
            <thead class="bg-slate-900">
                <tr class="text-left text-xs font-medium uppercase tracking-wide text-slate-500">
                    <th class="px-4 py-3">Nome</th>
                    <th class="px-4 py-3">Tenant</th>
                    <th class="px-4 py-3">Slug</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse ($applications as $application)
                    <tr class="bg-slate-900/60">
                        <td class="px-4 py-3 text-slate-200">{{ $application->name }}</td>
                        <td class="px-4 py-3 text-slate-400">{{ $application->tenant->name }}</td>
                        <td class="px-4 py-3 text-slate-500 font-mono text-xs">{{ $application->slug }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1.5 text-xs">
                                <span class="h-2 w-2 rounded-full {{ $application->is_active ? 'bg-emerald-400' : 'bg-rose-500' }}"></span>
                                {{ $application->is_active ? 'Ativa' : 'Inativa' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right space-x-3 whitespace-nowrap">
                            @can('manageTokens', $application)
                                <a href="{{ route('admin.applications.tokens', $application) }}" class="text-amber-400 hover:text-amber-300">Tokens</a>
                            @endcan
                            @can('update', $application)
                                <a href="{{ route('admin.applications.edit', $application) }}" class="text-amber-400 hover:text-amber-300">Editar</a>
                                <button type="button" wire:click="toggleActive({{ $application->id }})" class="text-slate-400 hover:text-white">
                                    {{ $application->is_active ? 'Desativar' : 'Ativar' }}
                                </button>
                            @endcan
                            @can('delete', $application)
                                <button type="button" wire:click="delete({{ $application->id }})"
                                        wire:confirm="Tem certeza que deseja excluir esta aplicação? Todos os tokens serão revogados."
                                        class="text-rose-400 hover:text-rose-300">
                                    Excluir
                                </button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr class="bg-slate-900/60">
                        <td class="px-4 py-6 text-center text-slate-500" colspan="5">Nenhuma aplicação encontrada.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $applications->links() }}
    </div>
</div>
