<div class="rounded-xl border border-slate-800 bg-slate-900 p-6">
    <h1 class="text-xl font-semibold text-white mb-6">Acesso administrativo</h1>

    <form wire:submit="authenticate" class="space-y-4">
        <div>
            <label for="email" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">E-mail</label>
            <input wire:model="email" type="email" id="email" autofocus autocomplete="username"
                   class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
            @error('email')
                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Senha</label>
            <div class="relative" x-data="{ show: false }">
                <input :type="show ? 'text' : 'password'" wire:model="password" id="password" autocomplete="current-password"
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

        <label class="flex items-center gap-2 text-sm text-slate-400">
            <input wire:model="remember" type="checkbox" class="rounded border-slate-700 bg-slate-950 text-amber-400 focus:ring-amber-400">
            Lembrar-me
        </label>

        <button type="submit"
                class="w-full rounded-lg bg-amber-400 px-3 py-2 text-sm font-semibold text-slate-950 hover:bg-amber-300"
                wire:loading.attr="disabled">
            Entrar
        </button>
    </form>
</div>
