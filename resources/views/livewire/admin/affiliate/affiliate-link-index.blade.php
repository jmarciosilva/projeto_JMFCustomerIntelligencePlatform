<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold">Links de Afiliado</h2>
        <a href="{{ route('admin.affiliate.links.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Novo Link
        </a>
    </div>

    @if (session()->has('message'))
        <div class="p-4 bg-green-100 text-green-800 rounded-lg">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('slug_copied'))
        <div class="p-4 bg-blue-100 text-blue-800 rounded-lg">
            Slug copiado: {{ session('slug_copied') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b space-y-3">
            <input
                type="text"
                wire:model.live="search"
                placeholder="Buscar por slug..."
                class="w-full px-4 py-2 border rounded-lg"
            >
            <select
                wire:model.live="status_filter"
                class="w-full px-4 py-2 border rounded-lg"
            >
                <option value="">Todos os status</option>
                <option value="active">Ativo</option>
                <option value="paused">Pausado</option>
                <option value="archived">Arquivado</option>
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Slug</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Produto</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Campanha</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Clicks</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($links as $link)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-mono text-sm">
                                <a href="{{ route('affiliate.redirect', ['slug' => $link->slug]) }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                                    {{ $link->slug }}
                                </a>
                            </td>
                            <td class="px-6 py-4 text-sm">{{ $link->affiliateProduct->product_name ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm">{{ $link->campaign->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm font-medium">{{ $link->clicks ?? 0 }}</td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-sm font-medium
                                    {{ $link->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $link->status === 'paused' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $link->status === 'archived' ? 'bg-gray-100 text-gray-800' : '' }}
                                ">
                                    {{ ucfirst($link->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 flex gap-2">
                                <a href="{{ route('admin.affiliate.links.edit', $link) }}" class="text-blue-600 hover:text-blue-800">
                                    Editar
                                </a>
                                <button wire:click="copySlug('{{ $link->slug }}')" class="text-green-600 hover:text-green-800 text-sm">
                                    Copiar
                                </button>
                                <button wire:click="delete({{ $link->id }})" onclick="return confirm('Tem certeza?')" class="text-red-600 hover:text-red-800">
                                    Deletar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                Nenhum link encontrado
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t">
            {{ $links->links() }}
        </div>
    </div>
</div>
