<div>
    <x-slot:header>Dashboard</x-slot:header>

    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-px overflow-hidden rounded-xl border border-slate-800 bg-slate-800 mb-8">
        <div class="bg-slate-900 p-5">
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Administradores</dt>
            <dd class="mt-1 text-2xl font-semibold text-white">{{ $totalAdmins }}</dd>
        </div>
        <div class="bg-slate-900 p-5">
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Ativos</dt>
            <dd class="mt-1 text-2xl font-semibold text-white">{{ $activeAdmins }}</dd>
        </div>
    </dl>

    <h2 class="text-sm font-medium uppercase tracking-wide text-slate-500 mb-3">Atividade recente</h2>

    <div class="overflow-hidden rounded-xl border border-slate-800">
        <table class="w-full text-sm">
            <tbody class="divide-y divide-slate-800">
                @forelse ($recentAuditLogs as $log)
                    <tr class="bg-slate-900">
                        <td class="px-4 py-3 text-slate-200">{{ $log->action }}</td>
                        <td class="px-4 py-3 text-slate-400">{{ $log->user?->name ?? 'Sistema' }}</td>
                        <td class="px-4 py-3 text-slate-500 text-right">{{ $log->created_at?->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr class="bg-slate-900">
                        <td class="px-4 py-6 text-center text-slate-500" colspan="3">Nenhuma atividade registrada ainda.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
