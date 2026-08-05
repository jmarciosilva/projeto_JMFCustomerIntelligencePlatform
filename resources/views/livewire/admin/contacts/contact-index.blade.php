<div>
    <x-slot:header>Contatos</x-slot:header>

    <div class="flex items-center justify-between mb-6 gap-4">
        <input wire:model.live.debounce.300ms="search" type="search" placeholder="Buscar por nome, email ou external_id..."
               class="w-full max-w-sm rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-800">
        <table class="w-full text-sm">
            <thead class="bg-slate-900">
                <tr class="text-left text-xs font-medium uppercase tracking-wide text-slate-500">
                    <th class="px-4 py-3">Nome</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">External ID</th>
                    <th class="px-4 py-3">Última atividade</th>
                    <th class="px-4 py-3 text-right">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse ($contacts as $contact)
                    <tr class="bg-slate-900/60">
                        <td class="px-4 py-3 text-slate-200">{{ $contact->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-400">{{ $contact->email ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-500 font-mono text-xs">{{ $contact->external_id ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-400">{{ $contact->last_seen_at?->diffForHumans() }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.contacts.show', $contact) }}" class="text-amber-400 hover:text-amber-300">Ver</a>
                        </td>
                    </tr>
                @empty
                    <tr class="bg-slate-900/60">
                        <td class="px-4 py-6 text-center text-slate-500" colspan="5">Nenhum contato encontrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $contacts->links() }}
    </div>
</div>
