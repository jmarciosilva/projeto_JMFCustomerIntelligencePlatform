<div>
    <x-slot:header>{{ $program ? 'Editar programa de afiliados' : 'Novo programa de afiliados' }}</x-slot:header>

    <x-slot:help>
        <x-help-modal title="Ajuda — Programa de Afiliados">
            <p>Escolha a Application (workspace) dona deste programa, um nome (ex.: "Magazine Você") e o status. O identificador único (slug) é gerado automaticamente.</p>
        </x-help-modal>
    </x-slot:help>

    <div class="max-w-lg rounded-xl border border-slate-800 bg-slate-900 p-6">
        <form wire:submit="save" class="space-y-4">
            <div>
                <label for="applicationId" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Application</label>
                <select wire:model="applicationId" id="applicationId" @disabled($program)
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
                <input wire:model="name" type="text" id="name"
                       class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                @error('name')
                    <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="website" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Website</label>
                <input wire:model="website" type="text" id="website" placeholder="https://..."
                       class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                @error('website')
                    <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Descrição</label>
                <textarea wire:model="description" id="description" rows="3"
                          class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400"></textarea>
                @error('description')
                    <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="status" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Status</label>
                <select wire:model="status" id="status"
                        class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                    <option value="active">Ativo</option>
                    <option value="inactive">Inativo</option>
                </select>
                @error('status')
                    <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="rounded-lg bg-amber-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-amber-300"
                        wire:loading.attr="disabled">
                    Salvar
                </button>
                <a href="{{ route('admin.affiliate.programs.index') }}" class="text-sm text-slate-400 hover:text-white">Cancelar</a>
            </div>
        </form>
    </div>
</div>
