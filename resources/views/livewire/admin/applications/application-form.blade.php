<div>
    <x-slot:header>{{ $application ? 'Editar aplicação' : 'Nova aplicação' }}</x-slot:header>

    <x-slot:help>
        <x-help-modal title="Ajuda — Aplicação">
            <p>Vincule a aplicação a um Tenant (não pode ser alterado depois de criada) e dê um nome descritivo.</p>
            <p><strong>Evento de conversão</strong> (opcional): informe o <code>event_name</code> que representa uma conversão para essa aplicação (ex.: <code>contact.form_submitted</code>). Isso alimenta o indicador de conversão no Analytics.</p>
        </x-help-modal>
    </x-slot:help>

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
                <label for="tenant_id" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Tenant</label>
                <select wire:model="tenant_id" id="tenant_id" @disabled($application)
                        class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400 disabled:opacity-50">
                    <option value="">Selecione...</option>
                    @foreach ($tenants as $tenant)
                        <option value="{{ $tenant->id }}">{{ $tenant->name }}</option>
                    @endforeach
                </select>
                @if ($application)
                    <p class="mt-1 text-xs text-slate-500">O tenant de uma aplicação não pode ser alterado após a criação.</p>
                @endif
                @error('tenant_id')
                    <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="conversion_event_name" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Evento de conversão (opcional)</label>
                <input wire:model="conversion_event_name" type="text" id="conversion_event_name" placeholder="ex.: contact.form_submitted"
                       class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                <p class="mt-1 text-xs text-slate-500">Nome do evento (`event_name`) que representa uma conversão desta aplicação. Usado no dashboard de Analytics.</p>
                @error('conversion_event_name')
                    <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="rounded-lg bg-amber-400 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-amber-300"
                        wire:loading.attr="disabled">
                    Salvar
                </button>
                <a href="{{ route('admin.applications.index') }}" class="text-sm text-slate-400 hover:text-white">Cancelar</a>
            </div>
        </form>
    </div>
</div>
