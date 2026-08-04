<div>
    <x-slot:header>{{ $user ? 'Editar usuário' : 'Novo usuário' }}</x-slot:header>

    <div class="max-w-lg rounded-xl border border-slate-800 bg-slate-900 p-6">
        <form wire:submit="save" class="space-y-4">
            <div>
                <label for="name" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Nome</label>
                <input wire:model="name" type="text" id="name"
                       class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                @error('name')
                    <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">E-mail</label>
                <input wire:model="email" type="email" id="email"
                       class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                @error('email')
                    <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">
                    Senha {{ $user ? '(deixe em branco para manter a atual)' : '' }}
                </label>
                <input wire:model="password" type="password" id="password" autocomplete="new-password"
                       class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                @error('password')
                    <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <span class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-2">Perfis</span>
                <div class="space-y-2">
                    @foreach ($availableRoles as $role)
                        <label class="flex items-center gap-2 text-sm text-slate-300">
                            <input type="checkbox" wire:model="selectedRoles" value="{{ $role->name }}"
                                   class="rounded border-slate-700 bg-slate-950 text-amber-400 focus:ring-amber-400">
                            {{ $role->name }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="rounded-lg bg-amber-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-amber-300"
                        wire:loading.attr="disabled">
                    Salvar
                </button>
                <a href="{{ route('admin.users.index') }}" class="text-sm text-slate-400 hover:text-white">Cancelar</a>
            </div>
        </form>
    </div>
</div>
