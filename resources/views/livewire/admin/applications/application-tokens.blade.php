<div>
    <x-slot:header>Tokens — {{ $application->name }}</x-slot:header>

    @if ($plainTextToken)
        <div class="mb-6 rounded-xl border border-amber-800 bg-amber-950/40 p-4">
            <p class="text-sm font-semibold text-amber-400 mb-2">Copie este token agora — ele não será exibido novamente.</p>
            <div class="flex items-center gap-3">
                <code class="flex-1 break-all rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-xs text-slate-200">{{ $plainTextToken }}</code>
                <button type="button" wire:click="dismissToken" class="shrink-0 text-xs font-medium text-slate-400 hover:text-white">
                    Ok, copiei
                </button>
            </div>
        </div>
    @endif

    <div class="max-w-lg rounded-xl border border-slate-800 bg-slate-900 p-6 mb-6">
        <form wire:submit="create" class="flex items-end gap-3">
            <div class="flex-1">
                <label for="tokenName" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Novo token</label>
                <input wire:model="tokenName" type="text" id="tokenName" placeholder="Ex.: Produção"
                       class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                @error('tokenName')
                    <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit"
                    class="shrink-0 rounded-lg bg-amber-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-amber-300"
                    wire:loading.attr="disabled">
                Criar token
            </button>
        </form>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-800">
        <table class="w-full text-sm">
            <thead class="bg-slate-900">
                <tr class="text-left text-xs font-medium uppercase tracking-wide text-slate-500">
                    <th class="px-4 py-3">Nome</th>
                    <th class="px-4 py-3">Criado em</th>
                    <th class="px-4 py-3">Último uso</th>
                    <th class="px-4 py-3 text-right">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse ($tokens as $token)
                    <tr class="bg-slate-900/60">
                        <td class="px-4 py-3 text-slate-200">{{ $token->name }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $token->created_at?->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $token->last_used_at?->format('d/m/Y H:i') ?? 'Nunca usado' }}</td>
                        <td class="px-4 py-3 text-right space-x-3 whitespace-nowrap">
                            <button type="button" wire:click="rotate({{ $token->id }})"
                                    wire:confirm="Rotacionar este token? O valor atual deixará de funcionar imediatamente."
                                    class="text-amber-400 hover:text-amber-300">
                                Rotacionar
                            </button>
                            <button type="button" wire:click="revoke({{ $token->id }})"
                                    wire:confirm="Revogar este token? Ele deixará de funcionar imediatamente."
                                    class="text-rose-400 hover:text-rose-300">
                                Revogar
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr class="bg-slate-900/60">
                        <td class="px-4 py-6 text-center text-slate-500" colspan="4">Nenhum token criado ainda.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        <a href="{{ route('admin.applications.index') }}" class="text-sm text-slate-400 hover:text-white">&larr; Voltar para aplicações</a>
    </div>
</div>
