<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Oportunidades de Produtos</h1>
            <p class="mt-2 text-gray-600">Gerencie, aprove e publique oportunidades de afiliação</p>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <div class="relative">
            <input
                type="text"
                wire:model.live="search"
                placeholder="Buscar por tendência ou produto..."
                class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:outline-none"
            >
        </div>

        <select
            wire:model.live="statusFilter"
            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:outline-none"
        >
            <option value="">Todos os status</option>
            @foreach($statuses as $status)
                <option value="{{ $status->value }}">{{ $status->label() }}</option>
            @endforeach
        </select>

        <select
            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:outline-none"
        >
            <option>Mais recentes</option>
            <option>Score mais alto</option>
            <option>Score mais baixo</option>
        </select>
    </div>

    <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white">
        <table class="w-full">
            <thead class="border-b border-gray-200 bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">
                        <button wire:click="sort('trend_id')" class="hover:text-blue-600">
                            Tendência
                            @if($sortBy === 'trend_id')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Produto</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">
                        <button wire:click="sort('discovery_opportunity_score')" class="hover:text-blue-600">
                            Oportunidade
                            @if($sortBy === 'discovery_opportunity_score')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Intenção</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($opportunities as $opp)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <button
                                wire:click="selectOpportunity({{ $opp->id }})"
                                class="font-medium text-blue-600 hover:underline"
                            >
                                {{ $opp->trend->term }}
                            </button>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $opp->affiliateProduct->name }}
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <div class="flex items-center">
                                <div class="mr-2 h-2 w-2 rounded-full"
                                    :style="{ backgroundColor: '{{ $opp->discovery_opportunity_score > 70 ? '#10b981' : ($opp->discovery_opportunity_score > 40 ? '#f59e0b' : '#ef4444') }}' }}"
                                ></div>
                                <span class="font-semibold">{{ $opp->discovery_opportunity_score }}%</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium"
                                style="background-color: {{ $opp->purchase_intent_label->color() }}20; color: {{ $opp->purchase_intent_label->color() }};"
                            >
                                {{ $opp->purchase_intent_label->label() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium"
                                style="background-color: {{ $opp->status_sprint_a->color() }}20; color: {{ $opp->status_sprint_a->color() }};"
                            >
                                {{ $opp->status_sprint_a->label() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <button
                                wire:click="selectOpportunity({{ $opp->id }})"
                                class="text-blue-600 hover:text-blue-900"
                            >
                                Curar
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            Nenhuma oportunidade encontrada
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $opportunities->links() }}
    </div>

    @if($selectedOpportunity)
        <livewire:admin.affiliate.product-opportunity-detail
            :opportunity-id="$selectedOpportunity"
            @opportunity-updated="selectOpportunity(null)"
        />
    @endif
</div>
