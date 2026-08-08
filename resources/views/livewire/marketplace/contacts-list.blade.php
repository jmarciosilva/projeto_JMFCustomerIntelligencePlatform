<div>
    <x-slot:header>👥 Contatos & Clientes</x-slot:header>

    <div class="space-y-6">

    <!-- Filtros e Busca -->
    <div class="bg-white rounded-lg shadow p-6 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Empresa/Tenant -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Empresa</label>
                <select wire:model.live="tenantId" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @foreach ($tenants as $t)
                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Busca -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Buscar por nome ou e-mail</label>
                <input type="text" wire:model.live="searchTerm" placeholder="Buscar..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <!-- Filtro de Status -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Filtrar por Status</label>
                <select wire:model.live="filterBy" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="all">Todos</option>
                    <option value="converted">✅ Clientes Convertidos</option>
                    <option value="abandoned">⏸️ Carrinho Abandonado</option>
                    <option value="pending">👁️ Em Navegação</option>
                </select>
            </div>
        </div>

        <!-- Estatísticas Rápidas -->
        <div class="grid grid-cols-3 gap-4 mt-4 pt-4 border-t">
            <div class="text-center">
                <p class="text-gray-500 text-sm">Total de Contatos</p>
                <p class="text-2xl font-bold text-gray-900">{{ $contacts->total() }}</p>
            </div>
            <div class="text-center">
                <p class="text-gray-500 text-sm">Página Atual</p>
                <p class="text-2xl font-bold text-gray-900">{{ $contacts->currentPage() }} de {{ $contacts->lastPage() }}</p>
            </div>
            <div class="text-center">
                <p class="text-gray-500 text-sm">Por Página</p>
                <p class="text-2xl font-bold text-gray-900">{{ $contacts->perPage() }}</p>
            </div>
        </div>
    </div>

    <!-- Tabela de Contatos -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                            wire:click="changeSortBy('name')">
                            Nome
                            @if($sortBy === 'name')
                                {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">E-mail</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                            wire:click="changeSortBy('lead_score')">
                            Lead Score
                            @if($sortBy === 'lead_score')
                                {{ $sortDirection === 'asc' ? '↑' : '↓' }}
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Eventos</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($contacts as $contact)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <p class="font-medium text-gray-900">{{ $contact->name ?? 'Sem nome' }}</p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $contact->email ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="w-20 bg-gray-200 rounded-full h-2">
                                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ min($contact->lead_score, 100) }}%"></div>
                                    </div>
                                    <span class="text-sm font-semibold text-gray-900">{{ $contact->lead_score ?? 0 }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($contact->status === 'converted')
                                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">✅ Cliente</span>
                                @elseif($contact->status === 'abandoned')
                                    <span class="px-3 py-1 bg-orange-100 text-orange-800 rounded-full text-xs font-medium">⏸️ Abandonado</span>
                                @else
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">👁️ Navegando</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $contact->event_count ?? 0 }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a href="/admin/marketplace/contacts/{{ $contact->id }}" class="text-blue-600 hover:text-blue-900 font-medium">
                                    Ver Jornada →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center">
                                <p class="text-gray-500 text-lg">Nenhum contato encontrado</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $contacts->links() }}
        </div>
    </div>
    </div>
</div>
