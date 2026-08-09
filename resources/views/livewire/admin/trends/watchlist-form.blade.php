<div>
    <x-slot:header>{{ $watchlist ? 'Editar watchlist' : 'Nova watchlist' }}</x-slot:header>

    <x-slot:help>
        <x-help-modal title="Ajuda — Watchlist">
            <p>Digite uma palavra-chave, hashtag ou categoria por linha (ou separadas por vírgula). Cada termo se torna uma Tendência monitorada individualmente.</p>
            <p>Remover um termo não apaga o histórico já coletado — a Tendência correspondente fica apenas "Inativa".</p>
        </x-help-modal>
    </x-slot:help>

    <div class="max-w-2xl rounded-xl border border-slate-800 bg-slate-900 p-6">
        <form wire:submit="save" class="space-y-4">
            <div>
                <label for="applicationId" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Application</label>
                <select wire:model="applicationId" id="applicationId" @disabled($watchlist)
                        class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400 disabled:opacity-60">
                    @foreach ($applications as $application)
                        <option value="{{ $application->id }}">{{ $application->name }}</option>
                    @endforeach
                </select>
                @error('applicationId')
                    <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="name" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Nome</label>
                <input wire:model="name" type="text" id="name" placeholder="ex.: Casa"
                       class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                @error('name')
                    <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Descrição</label>
                <textarea wire:model="description" id="description" rows="2"
                          class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400"></textarea>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label for="keywords" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Palavras-chave</label>
                    <textarea wire:model="keywords" id="keywords" rows="5" placeholder="cafeteira&#10;air fryer"
                              class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 font-mono focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400"></textarea>
                </div>
                <div>
                    <label for="hashtags" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Hashtags</label>
                    <textarea wire:model="hashtags" id="hashtags" rows="5" placeholder="#cafeteira&#10;#cantinhodocafe"
                              class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 font-mono focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400"></textarea>
                </div>
                <div>
                    <label for="categories" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Categorias</label>
                    <textarea wire:model="categories" id="categories" rows="5" placeholder="Casa&#10;Cozinha"
                              class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 font-mono focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400"></textarea>
                </div>
            </div>
            <p class="text-xs text-slate-500">Um termo por linha (ou separados por vírgula).</p>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="collectionFrequency" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Frequência de coleta</label>
                    <select wire:model="collectionFrequency" id="collectionFrequency"
                            class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                        <option value="daily">Diária</option>
                        <option value="weekly">Semanal</option>
                    </select>
                </div>
                <div>
                    <label for="status" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Status</label>
                    <select wire:model="status" id="status"
                            class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                        <option value="active">Ativa</option>
                        <option value="inactive">Inativa</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="rounded-lg bg-amber-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-amber-300"
                        wire:loading.attr="disabled">
                    Salvar
                </button>
                <a href="{{ route('admin.trends.watchlists.index') }}" class="text-sm text-slate-400 hover:text-white">Cancelar</a>
            </div>
        </form>
    </div>
</div>
