<div>
    <x-slot:header>{{ $product ? 'Editar produto de afiliado' : 'Novo produto de afiliado' }}</x-slot:header>

    <x-slot:help>
        <x-help-modal title="Ajuda — Produto de Afiliado">
            <p>Cadastre os dados do produto exatamente como aparecem no programa de afiliados (ex.: Magazine Você). O <strong>link de afiliado</strong> é a URL oficial fornecida pelo programa — não é o link de rastreamento próprio (isso vem na Fase 27).</p>
        </x-help-modal>
    </x-slot:help>

    <div class="max-w-2xl rounded-xl border border-slate-800 bg-slate-900 p-6">
        <form wire:submit="save" class="space-y-4">
            <div>
                <label for="affiliateProgramId" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Programa de afiliados</label>
                <select wire:model="affiliateProgramId" id="affiliateProgramId" @disabled($product)
                        class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400 disabled:opacity-60">
                    <option value="">Selecione...</option>
                    @foreach ($programs as $affiliateProgram)
                        <option value="{{ $affiliateProgram->id }}">{{ $affiliateProgram->name }}</option>
                    @endforeach
                </select>
                @error('affiliateProgramId')
                    <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Nome</label>
                    <input wire:model="name" type="text" id="name"
                           class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                    @error('name')
                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="externalProductId" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">ID externo (opcional)</label>
                    <input wire:model="externalProductId" type="text" id="externalProductId"
                           class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                </div>
            </div>

            <div>
                <label for="description" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Descrição</label>
                <textarea wire:model="description" id="description" rows="2"
                          class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="category" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Categoria</label>
                    <input wire:model="category" type="text" id="category"
                           class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                </div>
                <div>
                    <label for="brand" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Marca</label>
                    <input wire:model="brand" type="text" id="brand"
                           class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label for="price" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Preço (R$)</label>
                    <input wire:model="price" type="text" id="price"
                           class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                    @error('price')
                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="originalPrice" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Preço original (R$)</label>
                    <input wire:model="originalPrice" type="text" id="originalPrice"
                           class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                </div>
                <div>
                    <label for="commissionPercentage" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Comissão (%)</label>
                    <input wire:model="commissionPercentage" type="text" id="commissionPercentage"
                           class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                    @error('commissionPercentage')
                        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="affiliateUrl" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Link de afiliado (oficial)</label>
                <input wire:model="affiliateUrl" type="text" id="affiliateUrl" placeholder="https://..."
                       class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                @error('affiliateUrl')
                    <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="imageUrl" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Imagem (URL)</label>
                <input wire:model="imageUrl" type="text" id="imageUrl" placeholder="https://..."
                       class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
            </div>

            <div>
                <label for="availability" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Disponibilidade</label>
                <select wire:model="availability" id="availability"
                        class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                    <option value="in_stock">Em estoque</option>
                    <option value="out_of_stock">Fora de estoque</option>
                    <option value="unknown">Desconhecida</option>
                </select>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="rounded-lg bg-amber-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-amber-300"
                        wire:loading.attr="disabled">
                    Salvar
                </button>
                <a href="{{ route('admin.affiliate.products.index') }}" class="text-sm text-slate-400 hover:text-white">Cancelar</a>
            </div>
        </form>
    </div>
</div>
