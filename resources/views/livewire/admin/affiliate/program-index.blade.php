<div>
    <x-slot:header>Programas de Afiliados</x-slot:header>

    <x-slot:help>
        <x-help-modal title="Ajuda — Programas de Afiliados">
            <p>Um <strong>Programa de Afiliados</strong> representa uma plataforma como o Influenciador Magalu/Magazine Você, Amazon Associados, Mercado Livre ou Shopee.</p>
            <p>Cada programa pertence a uma Application (workspace) e possui seus próprios <strong>Produtos de Afiliados</strong> cadastrados manualmente ou importados via CSV.</p>
        </x-help-modal>
    </x-slot:help>

    @error('affiliate_program')
        <div class="mb-4 rounded-lg border border-rose-800 bg-rose-950/50 px-3 py-2 text-sm text-rose-400">
            {{ $message }}
        </div>
    @enderror

    <div class="flex flex-wrap items-center justify-between mb-6 gap-4">
        <div class="flex flex-wrap items-center gap-3">
            <select wire:model.live="applicationId"
                    class="rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                @foreach ($applications as $application)
                    <option value="{{ $application->id }}">{{ $application->name }}</option>
                @endforeach
            </select>

            <input wire:model.live.debounce.300ms="search" type="search" placeholder="Buscar por nome..."
                   class="w-full max-w-sm rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
        </div>

        @can('create', App\Models\AffiliateProgram::class)
            <a href="{{ route('admin.affiliate.programs.create') }}"
               class="shrink-0 rounded-lg bg-amber-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-amber-300">
                Novo programa
            </a>
        @endcan
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-800">
        <table class="w-full text-sm">
            <thead class="bg-slate-900">
                <tr class="text-left text-xs font-medium uppercase tracking-wide text-slate-500">
                    <th class="px-4 py-3">Nome</th>
                    <th class="px-4 py-3">Provider</th>
                    <th class="px-4 py-3">Produtos</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse ($programs as $program)
                    <tr class="bg-slate-900/60">
                        <td class="px-4 py-3 text-slate-200">{{ $program->name }}</td>
                        <td class="px-4 py-3 text-slate-500 font-mono text-xs">{{ $program->provider }}</td>
                        <td class="px-4 py-3 text-slate-400">
                            <a href="{{ route('admin.affiliate.products.index', ['program' => $program->id]) }}" class="text-amber-400 hover:text-amber-300">
                                {{ $program->products_count }}
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1.5 text-xs">
                                <span class="h-2 w-2 rounded-full {{ $program->isActive() ? 'bg-emerald-400' : 'bg-rose-500' }}"></span>
                                {{ $program->isActive() ? 'Ativo' : 'Inativo' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right space-x-3 whitespace-nowrap">
                            @can('update', $program)
                                <a href="{{ route('admin.affiliate.programs.edit', $program) }}" class="text-amber-400 hover:text-amber-300">Editar</a>
                            @endcan
                            @can('delete', $program)
                                <button type="button" wire:click="delete({{ $program->id }})"
                                        wire:confirm="Tem certeza que deseja excluir este programa de afiliados?"
                                        class="text-rose-400 hover:text-rose-300">
                                    Excluir
                                </button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr class="bg-slate-900/60">
                        <td class="px-4 py-6 text-center text-slate-500" colspan="5">Nenhum programa de afiliados encontrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $programs->links() }}
    </div>
</div>
