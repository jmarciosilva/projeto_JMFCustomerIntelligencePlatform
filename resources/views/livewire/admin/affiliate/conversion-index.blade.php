<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold">Conversões</h2>
        <div class="flex gap-2">
            <a href="{{ route('admin.affiliate.conversions.import') }}" class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700">
                Importar CSV
            </a>
            <a href="{{ route('admin.affiliate.conversions.create') }}" class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700">
                Nova Conversão
            </a>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="p-4 bg-emerald-500/20 text-emerald-300 rounded-lg">
            {{ session('message') }}
        </div>
    @endif

    <div class="bg-slate-900 rounded-lg shadow">
        <div class="p-4 border-b space-y-3">
            <input
                type="text"
                wire:model.live="search"
                placeholder="Buscar por referência de pedido..."
                class="w-full px-4 py-2 border rounded-lg"
            >
            <select
                wire:model.live="status_filter"
                class="w-full px-4 py-2 border rounded-lg"
            >
                <option value="">Todos os status</option>
                <option value="pending">Pendente</option>
                <option value="approved">Aprovado</option>
                <option value="paid">Pago</option>
                <option value="cancelled">Cancelado</option>
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-800 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Referência</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Produto</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Data</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Preço</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Comissão</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($conversions as $conversion)
                        <tr class="hover:bg-slate-800">
                            <td class="px-6 py-4 font-mono text-sm">{{ $conversion->order_reference }}</td>
                            <td class="px-6 py-4 text-sm">{{ $conversion->affiliateProduct->product_name ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm">{{ $conversion->order_date->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-sm">R$ {{ number_format($conversion->product_price, 2, ',', '.') }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-green-600">
                                R$ {{ number_format($conversion->commission_value, 2, ',', '.') }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-sm font-medium
                                    {{ $conversion->status === 'pending' ? 'bg-yellow-500/20 text-yellow-300' : '' }}
                                    {{ $conversion->status === 'approved' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $conversion->status === 'paid' ? 'bg-emerald-500/20 text-emerald-300' : '' }}
                                    {{ $conversion->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                                ">
                                    {{ ucfirst($conversion->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 flex gap-2">
                                @if ($conversion->isPending())
                                    <button wire:click="approve({{ $conversion->id }})" class="text-blue-600 hover:text-blue-800 text-sm">
                                        Aprovar
                                    </button>
                                @endif
                                @if ($conversion->isApproved())
                                    <button wire:click="markAsPaid({{ $conversion->id }})" class="text-green-600 hover:text-emerald-300 text-sm">
                                        Marcar Pago
                                    </button>
                                @endif
                                <a href="{{ route('admin.affiliate.conversions.edit', $conversion) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                                    Editar
                                </a>
                                <button wire:click="cancel({{ $conversion->id }})" onclick="return confirm('Tem certeza?')" class="text-red-600 hover:text-red-800 text-sm">
                                    Cancelar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-400">
                                Nenhuma conversão encontrada
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t">
            {{ $conversions->links() }}
        </div>
    </div>
</div>
