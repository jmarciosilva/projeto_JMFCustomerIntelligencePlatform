<div>
    <x-slot:header>Importar produtos via CSV</x-slot:header>

    <x-slot:help>
        <x-help-modal title="Ajuda — Importação CSV">
            <p>O arquivo CSV deve ter cabeçalho na primeira linha com as colunas: <code>external_product_id, name, description, category, brand, price, original_price, commission_percentage, estimated_commission, affiliate_url, image_url, availability</code>.</p>
            <p>Apenas <strong>name</strong> e <strong>affiliate_url</strong> são obrigatórios. Linhas inválidas são reportadas sem interromper a importação das demais.</p>
        </x-help-modal>
    </x-slot:help>

    <div class="max-w-lg rounded-xl border border-slate-800 bg-slate-900 p-6">
        <form wire:submit="import" class="space-y-4">
            <div>
                <label for="affiliateProgramId" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Programa de afiliados</label>
                <select wire:model="affiliateProgramId" id="affiliateProgramId"
                        class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                    @foreach ($programs as $affiliateProgram)
                        <option value="{{ $affiliateProgram->id }}">{{ $affiliateProgram->name }}</option>
                    @endforeach
                </select>
                @error('affiliateProgramId')
                    <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="file" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Arquivo CSV</label>
                <input wire:model="file" type="file" id="file" accept=".csv,.txt"
                       class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                @error('file')
                    <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="rounded-lg bg-amber-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-amber-300"
                        wire:loading.attr="disabled">
                    Importar
                </button>
                <a href="{{ route('admin.affiliate.products.index') }}" class="text-sm text-slate-400 hover:text-white">Voltar</a>
            </div>
        </form>

        @if ($summary !== null)
            <div class="mt-6 rounded-lg border border-slate-700 bg-slate-950 p-4 text-sm">
                <p class="text-slate-200">
                    <strong class="text-emerald-400">{{ $summary['processed'] }}</strong> produto(s) importado(s) com sucesso,
                    <strong class="{{ $summary['failed'] > 0 ? 'text-rose-400' : 'text-slate-400' }}">{{ $summary['failed'] }}</strong> linha(s) com erro.
                </p>
                @if ($summary['errors'] !== [])
                    <ul class="mt-2 list-disc list-inside text-rose-400 text-xs space-y-1">
                        @foreach ($summary['errors'] as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif
    </div>
</div>
