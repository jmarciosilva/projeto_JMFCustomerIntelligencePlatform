<div>
    <x-slot:header>Produtos de Afiliados</x-slot:header>

    <x-slot:help>
        <x-help-modal title="Ajuda — Produtos de Afiliados">
            <p>Produtos cadastrados manualmente ou importados via CSV para cada Programa de Afiliados. Esses produtos serão relacionados a tendências detectadas nas próximas fases (Product Matcher, Fase 25).</p>
        </x-help-modal>
    </x-slot:help>

    <div class="flex flex-wrap items-center justify-between mb-6 gap-4">
        <div class="flex flex-wrap items-center gap-3">
            <select wire:model.live="affiliateProgramId"
                    class="rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                <option value="">Todos os programas</option>
                @foreach ($programs as $affiliateProgram)
                    <option value="{{ $affiliateProgram->id }}">{{ $affiliateProgram->name }}</option>
                @endforeach
            </select>

            <input wire:model.live.debounce.300ms="search" type="search" placeholder="Buscar por nome..."
                   class="w-full max-w-sm rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
        </div>

        <div class="flex items-center gap-3">
            @can('create', App\Models\AffiliateProduct::class)
                <a href="{{ route('admin.affiliate.products.import') }}"
                   class="shrink-0 rounded-lg border border-amber-400 px-4 py-2 text-sm font-semibold text-amber-400 hover:bg-amber-400/10">
                    Importar CSV
                </a>
                <a href="{{ route('admin.affiliate.products.create') }}"
                   class="shrink-0 rounded-lg bg-amber-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-amber-300">
                    Novo produto
                </a>
            @endcan
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-800">
        <table class="w-full text-sm">
            <thead class="bg-slate-900">
                <tr class="text-left text-xs font-medium uppercase tracking-wide text-slate-500">
                    <th class="px-4 py-3">Nome</th>
                    <th class="px-4 py-3">Programa</th>
                    <th class="px-4 py-3">Preço</th>
                    <th class="px-4 py-3">Comissão</th>
                    <th class="px-4 py-3">Disponibilidade</th>
                    <th class="px-4 py-3 text-right">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse ($products as $product)
                    <tr class="bg-slate-900/60">
                        <td class="px-4 py-3 text-slate-200">{{ $product->name }}</td>
                        <td class="px-4 py-3 text-slate-400">{{ $product->affiliateProgram->name }}</td>
                        <td class="px-4 py-3 text-slate-400">
                            @if ($product->price !== null)
                                R$ {{ number_format((float) $product->price, 2, ',', '.') }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-400">
                            {{ $product->commission_percentage !== null ? number_format((float) $product->commission_percentage, 2, ',', '.').'%' : '—' }}
                        </td>
                        <td class="px-4 py-3 text-slate-400">{{ $product->availability }}</td>
                        <td class="px-4 py-3 text-right space-x-3 whitespace-nowrap">
                            @can('update', $product)
                                <a href="{{ route('admin.affiliate.products.edit', $product) }}" class="text-amber-400 hover:text-amber-300">Editar</a>
                            @endcan
                            @can('delete', $product)
                                <button type="button" wire:click="delete({{ $product->id }})"
                                        wire:confirm="Tem certeza que deseja excluir este produto?"
                                        class="text-rose-400 hover:text-rose-300">
                                    Excluir
                                </button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr class="bg-slate-900/60">
                        <td class="px-4 py-6 text-center text-slate-500" colspan="6">Nenhum produto de afiliado encontrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $products->links() }}
    </div>
</div>
