<div>
    <x-slot:header>Usuários</x-slot:header>

    <div class="flex items-center justify-between mb-6 gap-4">
        <input wire:model.live.debounce.300ms="search" type="search" placeholder="Buscar por nome ou e-mail..."
               class="w-full max-w-sm rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">

        @can('create', App\Models\User::class)
            <a href="{{ route('admin.users.create') }}"
               class="shrink-0 rounded-lg bg-amber-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-amber-300">
                Novo usuário
            </a>
        @endcan
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-800">
        <table class="w-full text-sm">
            <thead class="bg-slate-900">
                <tr class="text-left text-xs font-medium uppercase tracking-wide text-slate-500">
                    <th class="px-4 py-3">Nome</th>
                    <th class="px-4 py-3">E-mail</th>
                    <th class="px-4 py-3">Perfis</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse ($users as $user)
                    <tr class="bg-slate-900/60">
                        <td class="px-4 py-3 text-slate-200">{{ $user->name }}</td>
                        <td class="px-4 py-3 text-slate-400">{{ $user->email }}</td>
                        <td class="px-4 py-3 text-slate-400">{{ $user->roles->pluck('name')->join(', ') ?: '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1.5 text-xs">
                                <span class="h-2 w-2 rounded-full {{ $user->is_active ? 'bg-emerald-400' : 'bg-rose-500' }}"></span>
                                {{ $user->is_active ? 'Ativo' : 'Inativo' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right space-x-3 whitespace-nowrap">
                            @can('update', $user)
                                <a href="{{ route('admin.users.edit', $user) }}" class="text-amber-400 hover:text-amber-300">Editar</a>
                                <button type="button" wire:click="toggleActive({{ $user->id }})" class="text-slate-400 hover:text-white">
                                    {{ $user->is_active ? 'Desativar' : 'Ativar' }}
                                </button>
                            @endcan
                            @can('delete', $user)
                                <button type="button" wire:click="delete({{ $user->id }})"
                                        wire:confirm="Tem certeza que deseja excluir este usuário?"
                                        class="text-rose-400 hover:text-rose-300">
                                    Excluir
                                </button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr class="bg-slate-900/60">
                        <td class="px-4 py-6 text-center text-slate-500" colspan="5">Nenhum usuário encontrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
</div>
