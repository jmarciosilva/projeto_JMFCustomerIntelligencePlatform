<div>
    <x-slot:header>👥 Contatos & Clientes</x-slot:header>

    <div class="space-y-6">

    <!-- Filtros e Busca -->
    <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Empresa/Tenant -->
            <div>
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Empresa</label>
                <select wire:model.live="tenantId"
                        class="w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                    @foreach ($tenants as $t)
                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Busca -->
            <div>
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Buscar por nome ou e-mail</label>
                <input type="text" wire:model.live="searchTerm" placeholder="Buscar..."
                    class="w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-600 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
            </div>

            <!-- Filtro de Status -->
            <div>
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Filtrar por Status</label>
                <select wire:model.live="filterBy"
                        class="w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                    <option value="all">Todos</option>
                    <option value="converted">✅ Clientes Convertidos</option>
                    <option value="abandoned">⏸️ Carrinho Abandonado</option>
                    <option value="pending">👁️ Em Navegação</option>
                </select>
            </div>
        </div>

        <!-- Estatísticas Rápidas -->
        <div class="grid grid-cols-3 gap-4 mt-4 pt-4 border-t border-slate-800">
            <div class="text-center">
                <p class="text-xs text-slate-500">Total de Contatos</p>
                <p class="text-xl font-semibold text-white">{{ $contacts->total() }}</p>
            </div>
            <div class="text-center">
                <p class="text-xs text-slate-500">Página Atual</p>
                <p class="text-xl font-semibold text-white">{{ $contacts->currentPage() }} de {{ $contacts->lastPage() }}</p>
            </div>
            <div class="text-center">
                <p class="text-xs text-slate-500">Por Página</p>
                <p class="text-xl font-semibold text-white">{{ $contacts->perPage() }}</p>
            </div>
        </div>
    </div>

    <!-- Tabela de Contatos -->
    <div class="rounded-xl border border-slate-800 bg-slate-900/60 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-950/40 border-b border-slate-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 cursor-pointer hover:text-white"
                            wire:click="changeSortBy('name')">
                            Nome
                            @if($sortBy === 'name')
                                {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500">E-mail</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500 cursor-pointer hover:text-white"
                            wire:click="changeSortBy('lead_score')">
                            Lead Score
                            @if($sortBy === 'lead_score')
                                {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Eventos</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($contacts as $contact)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="font-medium text-white">{{ $contact->name ?? 'Sem nome' }}</p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-slate-400">{{ $contact->email ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="w-20 bg-slate-800 rounded-full h-1.5">
                                        <div class="bg-amber-400 h-1.5 rounded-full" style="width: {{ min($contact->lead_score, 100) }}%"></div>
                                    </div>
                                    <span class="font-semibold text-white">{{ $contact->lead_score ?? 0 }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($contact->status === 'converted')
                                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-emerald-400/10 text-emerald-400">✅ Cliente</span>
                                @elseif($contact->status === 'abandoned')
                                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-amber-400/10 text-amber-400">⏸️ Abandonado</span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-blue-400/10 text-blue-400">👁️ Navegando</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-slate-300">
                                {{ $contact->event_count ?? 0 }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="/admin/marketplace/contacts/{{ $contact->id }}" class="text-amber-400 hover:text-amber-300 font-medium">
                                    Ver Jornada →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center">
                                <p class="text-slate-500">Nenhum contato encontrado</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        <div class="px-6 py-4 border-t border-slate-800">
            {{ $contacts->links() }}
        </div>
    </div>
    </div>
</div>
