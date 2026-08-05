<div>
    <x-slot:header>Meu perfil</x-slot:header>

    <x-slot:help>
        <x-help-modal title="Ajuda — Meu perfil">
            <p>Altere seu próprio nome e/ou senha. Para trocar a senha, é preciso informar a senha atual — por segurança, mesmo Super Admins não podem pular essa confirmação.</p>
        </x-help-modal>
    </x-slot:help>

    <div class="max-w-lg rounded-xl border border-slate-800 bg-slate-900 p-6">
        @if ($saved)
            <div class="mb-4 rounded-lg border border-emerald-800 bg-emerald-950/50 px-3 py-2 text-sm text-emerald-400">
                Perfil atualizado com sucesso.
            </div>
        @endif

        <form wire:submit="save" class="space-y-4">
            <div>
                <label for="name" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Nome</label>
                <input wire:model="name" type="text" id="name"
                       class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                @error('name')
                    <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-2 border-t border-slate-800">
                <p class="text-xs text-slate-500 mb-4">Preencha os campos abaixo apenas se quiser alterar sua senha.</p>

                <div class="space-y-4">
                    <div>
                        <label for="current_password" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Senha atual</label>
                        <div class="relative" x-data="{ show: false }">
                            <input :type="show ? 'text' : 'password'" wire:model="current_password" id="current_password" autocomplete="current-password"
                                   class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 pr-16 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                            <button type="button" @click="show = !show" tabindex="-1"
                                    class="absolute inset-y-0 right-0 flex items-center px-3 text-xs font-medium text-slate-500 hover:text-amber-400">
                                <span x-text="show ? 'Ocultar' : 'Mostrar'"></span>
                            </button>
                        </div>
                        @error('current_password')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Nova senha</label>
                        <div class="relative" x-data="{ show: false }">
                            <input :type="show ? 'text' : 'password'" wire:model="password" id="password" autocomplete="new-password"
                                   class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 pr-16 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                            <button type="button" @click="show = !show" tabindex="-1"
                                    class="absolute inset-y-0 right-0 flex items-center px-3 text-xs font-medium text-slate-500 hover:text-amber-400">
                                <span x-text="show ? 'Ocultar' : 'Mostrar'"></span>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Confirmar nova senha</label>
                        <input wire:model="password_confirmation" type="password" id="password_confirmation" autocomplete="new-password"
                               class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                    </div>
                </div>
            </div>

            <div class="pt-2">
                <button type="submit"
                        class="rounded-lg bg-amber-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-amber-300"
                        wire:loading.attr="disabled">
                    Salvar
                </button>
            </div>
        </form>
    </div>
</div>
