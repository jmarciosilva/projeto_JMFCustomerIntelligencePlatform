<div>
    <x-slot:header>Tenants</x-slot:header>

    <x-slot:help>
        <x-help-modal title="Ajuda — Tenants">
            <p>Um <strong>Tenant</strong> representa uma empresa ou cliente da JMF System (ex.: a própria "JMF System" como dona de vários produtos, ou um cliente específico).</p>
            <p>Cada Tenant pode ter uma ou mais <strong>Aplicações</strong> vinculadas (ex.: Site Pessoal, Clube do Salão). Um Tenant só pode ser excluído se não tiver nenhuma aplicação vinculada.</p>
        </x-help-modal>
    </x-slot:help>

    @error('tenant')
        <div class="mb-4 rounded-lg border border-rose-800 bg-rose-950/50 px-3 py-2 text-sm text-rose-400">
            {{ $message }}
        </div>
    @enderror

    <div class="flex items-center justify-between mb-6 gap-4">
        <input wire:model.live.debounce.300ms="search" type="search" placeholder="Buscar por nome..."
               class="w-full max-w-sm rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">

        @can('create', App\Models\Tenant::class)
            <a href="{{ route('admin.tenants.create') }}"
               class="shrink-0 rounded-lg bg-amber-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-amber-300">
                Novo tenant
            </a>
        @endcan
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-800">
        <table class="w-full text-sm">
            <thead class="bg-slate-900">
                <tr class="text-left text-xs font-medium uppercase tracking-wide text-slate-500">
                    <th class="px-4 py-3">Nome</th>
                    <th class="px-4 py-3">Slug</th>
                    <th class="px-4 py-3">Applications</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse ($tenants as $tenant)
                    <tr class="bg-slate-900/60">
                        <td class="px-4 py-3 text-slate-200">{{ $tenant->name }}</td>
                        <td class="px-4 py-3 text-slate-500 font-mono text-xs">{{ $tenant->slug }}</td>
                        <td class="px-4 py-3 text-slate-400">{{ $tenant->applications_count }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1.5 text-xs">
                                <span class="h-2 w-2 rounded-full {{ $tenant->is_active ? 'bg-emerald-400' : 'bg-rose-500' }}"></span>
                                {{ $tenant->is_active ? 'Ativo' : 'Inativo' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right space-x-3 whitespace-nowrap">
                            @can('update', $tenant)
                                <a href="{{ route('admin.tenants.edit', $tenant) }}" class="text-amber-400 hover:text-amber-300">Editar</a>
                                <button type="button" wire:click="toggleActive({{ $tenant->id }})" class="text-slate-400 hover:text-white">
                                    {{ $tenant->is_active ? 'Desativar' : 'Ativar' }}
                                </button>
                            @endcan
                            @can('delete', $tenant)
                                <button type="button" wire:click="delete({{ $tenant->id }})"
                                        wire:confirm="Tem certeza que deseja excluir este tenant?"
                                        class="text-rose-400 hover:text-rose-300">
                                    Excluir
                                </button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr class="bg-slate-900/60">
                        <td class="px-4 py-6 text-center text-slate-500" colspan="5">Nenhum tenant encontrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $tenants->links() }}
    </div>
</div>
