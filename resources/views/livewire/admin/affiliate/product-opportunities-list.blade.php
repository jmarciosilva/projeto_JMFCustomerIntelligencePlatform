<div class="space-y-6">
    <div class="flex flex-wrap items-center gap-4 mb-6">
        <div>
            <label for="search" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Buscar</label>
            <input
                id="search"
                type="text"
                wire:model.live="search"
                placeholder="Buscar por tendência ou produto..."
                class="rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400"
            >
        </div>

        <div>
            <label for="statusFilter" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Status</label>
            <select
                id="statusFilter"
                wire:model.live="statusFilter"
                class="rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400"
            >
                <option value="">Todos os status</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="sortBy" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Ordenar</label>
            <select
                id="sortBy"
                class="rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400"
            >
                <option>Mais recentes</option>
                <option>Score mais alto</option>
                <option>Score mais baixo</option>
            </select>
        </div>
    </div>

    <div class="overflow-x-auto rounded-xl border border-slate-800 bg-slate-900/60">
        <table class="w-full">
            <thead class="border-b border-slate-800 bg-slate-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500">
                        <button wire:click="sort('trend_id')" class="hover:text-amber-400">
                            Tendência
                            @if($sortBy === 'trend_id')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Produto</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500">
                        <button wire:click="sort('discovery_opportunity_score')" class="hover:text-amber-400">
                            Oportunidade
                            @if($sortBy === 'discovery_opportunity_score')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </button>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Intenção</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-slate-500">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($opportunities as $opp)
                    <tr class="hover:bg-slate-800/50 transition-colors">
                        <td class="px-6 py-4 text-sm text-slate-100">
                            <button
                                wire:click="selectOpportunity({{ $opp->id }})"
                                class="font-medium text-amber-400 hover:text-amber-300"
                            >
                                {{ $opp->trend->term }}
                            </button>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-400">
                            {{ $opp->affiliateProduct->name }}
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <div class="flex items-center">
                                <div class="mr-2 h-2 w-2 rounded-full"
                                    :style="{ backgroundColor: '{{ $opp->discovery_opportunity_score > 70 ? '#10b981' : ($opp->discovery_opportunity_score > 40 ? '#f59e0b' : '#ef4444') }}' }}"
                                ></div>
                                <span class="font-semibold text-slate-100">{{ $opp->discovery_opportunity_score }}%</span>
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
                                class="text-amber-400 hover:text-amber-300 font-medium"
                            >
                                Curar
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-500">
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
