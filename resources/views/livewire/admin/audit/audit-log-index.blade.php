<div>
    <x-slot:header>Auditoria</x-slot:header>

    <div class="overflow-hidden rounded-xl border border-slate-800">
        <table class="w-full text-sm">
            <thead class="bg-slate-900">
                <tr class="text-left text-xs font-medium uppercase tracking-wide text-slate-500">
                    <th class="px-4 py-3">Ação</th>
                    <th class="px-4 py-3">Usuário</th>
                    <th class="px-4 py-3">Descrição</th>
                    <th class="px-4 py-3">IP</th>
                    <th class="px-4 py-3 text-right">Data</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse ($logs as $log)
                    <tr class="bg-slate-900/60 align-top">
                        <td class="px-4 py-3 text-slate-200 font-mono text-xs">{{ $log->action }}</td>
                        <td class="px-4 py-3 text-slate-400">{{ $log->user?->name ?? 'Sistema' }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $log->description ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $log->ip_address ?? '—' }}</td>
                        <td class="px-4 py-3 text-right text-slate-500 whitespace-nowrap">{{ $log->created_at?->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr class="bg-slate-900/60">
                        <td class="px-4 py-6 text-center text-slate-500" colspan="5">Nenhum registro de auditoria ainda.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $logs->links() }}
    </div>
</div>
