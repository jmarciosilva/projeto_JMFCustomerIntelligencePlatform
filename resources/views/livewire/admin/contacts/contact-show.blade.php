<div>
    <x-slot:header>{{ $contact->name ?? $contact->email ?? 'Contato #'.$contact->id }}</x-slot:header>

    <x-slot:help>
        <x-help-modal title="Ajuda — Detalhe do contato">
            <p><strong>Dados do contato</strong>: informações conhecidas e o Lead Score atual (recalculado diariamente).</p>
            <p><strong>Consentimentos</strong>: registro LGPD do que essa pessoa autorizou (ex.: marketing, analytics), capturado via SDK.</p>
            <p><strong>Timeline</strong>: todos os eventos gerados por essa pessoa, em qualquer aplicação, do mais recente ao mais antigo.</p>
        </x-help-modal>
    </x-slot:help>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-1 space-y-6">
            <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5">
                <h3 class="text-xs font-medium uppercase tracking-wide text-slate-500 mb-3">Dados do contato</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-500">Nome</dt>
                        <dd class="text-slate-200 text-right">{{ $contact->name ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-500">Email</dt>
                        <dd class="text-slate-200 text-right">{{ $contact->email ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-500">Telefone</dt>
                        <dd class="text-slate-200 text-right">{{ $contact->phone ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-500">External ID</dt>
                        <dd class="text-slate-200 text-right font-mono text-xs">{{ $contact->external_id ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-500">Primeira identificação</dt>
                        <dd class="text-slate-200 text-right">{{ $contact->first_identified_at?->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-500">Última atividade</dt>
                        <dd class="text-slate-200 text-right">{{ $contact->last_seen_at?->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-slate-500">Lead Score</dt>
                        <dd class="text-slate-200 text-right">
                            {{ $contact->lead_score }}
                            @if ($contact->lead_score_computed_at)
                                <span class="text-xs text-slate-500">(calculado {{ $contact->lead_score_computed_at->diffForHumans() }})</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5">
                <h3 class="text-xs font-medium uppercase tracking-wide text-slate-500 mb-3">Consentimentos</h3>
                @forelse ($consents as $consent)
                    <div class="flex items-center justify-between gap-2 py-1.5 text-sm">
                        <span class="text-slate-300 capitalize">{{ $consent->purpose }}</span>
                        <span class="inline-flex items-center gap-1.5 text-xs">
                            <span class="h-2 w-2 rounded-full {{ $consent->granted ? 'bg-emerald-400' : 'bg-rose-500' }}"></span>
                            {{ $consent->granted ? 'Concedido' : 'Revogado' }}
                        </span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Nenhum consentimento registrado.</p>
                @endforelse
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5">
                <h3 class="text-xs font-medium uppercase tracking-wide text-slate-500 mb-3">Timeline</h3>
                <ol class="space-y-3">
                    @forelse ($events as $event)
                        <li class="border-l-2 border-slate-800 pl-4 py-1">
                            <p class="text-sm text-slate-200">{{ $event->event_name }}</p>
                            <p class="text-xs text-slate-500">{{ $event->occurred_at->format('d/m/Y H:i:s') }}</p>
                        </li>
                    @empty
                        <li class="text-sm text-slate-500">Nenhum evento registrado para este contato.</li>
                    @endforelse
                </ol>

                <div class="mt-4">
                    {{ $events->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
