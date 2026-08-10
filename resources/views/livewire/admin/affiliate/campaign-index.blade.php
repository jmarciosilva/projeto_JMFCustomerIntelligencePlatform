<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold">Campanhas</h2>
        <a href="{{ route('admin.affiliate.campaigns.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Nova Campanha
        </a>
    </div>

    @if (session()->has('message'))
        <div class="p-4 bg-green-100 text-green-800 rounded-lg">
            {{ session('message') }}
        </div>
    @endif

    <div class="bg-slate-900 rounded-lg shadow">
        <div class="p-4 border-b">
            <input
                type="text"
                wire:model.live="search"
                placeholder="Buscar campanhas..."
                class="w-full px-4 py-2 border rounded-lg"
            >
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Nome</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Período</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Clicks / Conversões</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach ($campaigns as $campaign)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium">{{ $campaign->name }}</td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-sm font-medium
                                    {{ $campaign->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $campaign->status === 'paused' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $campaign->status === 'archived' ? 'bg-gray-100 text-gray-800' : '' }}
                                ">
                                    {{ ucfirst($campaign->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $campaign->start_date?->format('d/m/Y') ?? '-' }} até {{ $campaign->end_date?->format('d/m/Y') ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                {{ $campaign->affiliateLinks_count ?? 0 }} links
                            </td>
                            <td class="px-6 py-4 flex gap-2">
                                <a href="{{ route('admin.affiliate.campaigns.edit', $campaign) }}" class="text-blue-600 hover:text-blue-800">
                                    Editar
                                </a>
                                <button wire:click="delete({{ $campaign->id }})" onclick="return confirm('Tem certeza?')" class="text-red-600 hover:text-red-800">
                                    Deletar
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t">
            {{ $campaigns->links() }}
        </div>
    </div>
</div>
