<div>
    <x-slot:header>Analytics</x-slot:header>

    <div class="flex flex-wrap items-center gap-4 mb-6">
        <div>
            <label for="applicationId" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Aplicação</label>
            <select wire:model.live="applicationId" id="applicationId"
                    class="rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                @foreach ($applications as $app)
                    <option value="{{ $app->id }}">{{ $app->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="period" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Período</label>
            <select wire:model.live="period" id="period"
                    class="rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                <option value="today">Hoje</option>
                <option value="7d">Últimos 7 dias</option>
                <option value="30d">Últimos 30 dias</option>
                <option value="90d">Últimos 90 dias</option>
            </select>
        </div>
    </div>

    @if (! $application)
        <p class="text-sm text-slate-500">Nenhuma aplicação cadastrada ainda.</p>
    @else
        <dl class="grid grid-cols-2 lg:grid-cols-4 gap-px overflow-hidden rounded-xl border border-slate-800 bg-slate-800 mb-8">
            <div class="bg-slate-900 p-5">
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Eventos</dt>
                <dd class="mt-1 text-2xl font-semibold text-white">{{ number_format($overview['totals']['events_total']) }}</dd>
            </div>
            <div class="bg-slate-900 p-5">
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Visitantes únicos</dt>
                <dd class="mt-1 text-2xl font-semibold text-white">{{ number_format($overview['totals']['visitors_unique']) }}</dd>
            </div>
            <div class="bg-slate-900 p-5">
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Sessões únicas</dt>
                <dd class="mt-1 text-2xl font-semibold text-white">{{ number_format($overview['totals']['sessions_unique']) }}</dd>
            </div>
            <div class="bg-slate-900 p-5">
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Conversões</dt>
                <dd class="mt-1 text-2xl font-semibold text-white">
                    @if ($conversions)
                        {{ number_format($conversions['conversions']) }}
                        <span class="text-sm font-normal text-slate-500">({{ $conversions['rate'] }}%)</span>
                    @else
                        <span class="text-sm font-normal text-slate-500">Não configurado</span>
                    @endif
                </dd>
            </div>
        </dl>

        <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5 mb-8">
            <h3 class="text-xs font-medium uppercase tracking-wide text-slate-500 mb-3">Eventos por dia</h3>

            @php
                $trend = $overview['trend'];
                $maxEvents = max(1, collect($trend)->max('events_total'));
                $barCount = count($trend);
                $chartWidth = 600;
                $chartHeight = 120;
                $gap = 2;
                $barWidth = $barCount > 0 ? max(2, ($chartWidth - $gap * ($barCount - 1)) / $barCount) : 0;
            @endphp

            <svg viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}" preserveAspectRatio="none" class="w-full h-32" role="img" aria-label="Eventos por dia no período selecionado">
                @foreach ($trend as $index => $day)
                    @php
                        $barHeight = max(2, ($day['events_total'] / $maxEvents) * ($chartHeight - 4));
                        $x = $index * ($barWidth + $gap);
                        $y = $chartHeight - $barHeight;
                    @endphp
                    <rect x="{{ $x }}" y="{{ $y }}" width="{{ $barWidth }}" height="{{ $barHeight }}" rx="2" class="fill-amber-400">
                        <title>{{ \Illuminate\Support\Carbon::parse($day['date'])->format('d/m/Y') }}: {{ $day['events_total'] }} eventos</title>
                    </rect>
                @endforeach
            </svg>
        </div>

        <div class="grid gap-6 lg:grid-cols-2 mb-8">
            <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5">
                <h3 class="text-xs font-medium uppercase tracking-wide text-slate-500 mb-3">Páginas mais acessadas</h3>
                <ul class="space-y-2 text-sm">
                    @forelse ($topPages as $page)
                        <li class="flex justify-between gap-2">
                            <span class="text-slate-300 truncate">{{ $page['page_url'] }}</span>
                            <span class="text-slate-500 shrink-0">{{ $page['total'] }}</span>
                        </li>
                    @empty
                        <li class="text-slate-500">Sem dados no período.</li>
                    @endforelse
                </ul>
            </div>

            <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5">
                <h3 class="text-xs font-medium uppercase tracking-wide text-slate-500 mb-3">UTMs</h3>
                <ul class="space-y-2 text-sm">
                    @forelse ($utmBreakdown as $utm)
                        <li class="flex justify-between gap-2">
                            <span class="text-slate-300 truncate">{{ $utm['utm_source'] }} / {{ $utm['utm_medium'] ?? '—' }} / {{ $utm['utm_campaign'] ?? '—' }}</span>
                            <span class="text-slate-500 shrink-0">{{ $utm['total'] }}</span>
                        </li>
                    @empty
                        <li class="text-slate-500">Sem dados de UTM no período.</li>
                    @endforelse
                </ul>
            </div>

            <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5">
                <h3 class="text-xs font-medium uppercase tracking-wide text-slate-500 mb-3">Artigos mais acessados</h3>
                <ul class="space-y-2 text-sm">
                    @forelse ($topArticles as $article)
                        <li class="flex justify-between gap-2">
                            <span class="text-slate-300 truncate">#{{ $article['subject_id'] }}</span>
                            <span class="text-slate-500 shrink-0">{{ $article['total'] }}</span>
                        </li>
                    @empty
                        <li class="text-slate-500">Sem dados no período.</li>
                    @endforelse
                </ul>
            </div>

            <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5">
                <h3 class="text-xs font-medium uppercase tracking-wide text-slate-500 mb-3">Serviços mais acessados</h3>
                <ul class="space-y-2 text-sm">
                    @forelse ($topServices as $service)
                        <li class="flex justify-between gap-2">
                            <span class="text-slate-300 truncate">#{{ $service['subject_id'] }}</span>
                            <span class="text-slate-500 shrink-0">{{ $service['total'] }}</span>
                        </li>
                    @empty
                        <li class="text-slate-500">Sem dados no período.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-xs font-medium uppercase tracking-wide text-slate-500">Funil</h3>
                <select wire:model.live="funnelKey"
                        class="rounded-lg border border-slate-700 bg-slate-950 px-2 py-1 text-xs text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                    @foreach ($funnelTemplates as $key => $template)
                        <option value="{{ $key }}">{{ $template['label'] }}</option>
                    @endforeach
                </select>
            </div>

            @php
                $funnelMax = max(1, collect($funnel)->max('visitors') ?? 1);
            @endphp

            <ol class="space-y-3">
                @foreach ($funnel as $step)
                    <li>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-slate-300 font-mono text-xs">{{ $step['event_name'] }}</span>
                            <span class="text-slate-400">{{ $step['visitors'] }} visitantes ({{ $step['conversion_rate'] }}%)</span>
                        </div>
                        <div class="h-2 rounded-full bg-slate-800 overflow-hidden">
                            <div class="h-full rounded-full bg-amber-400" style="width: {{ ($step['visitors'] / $funnelMax) * 100 }}%"></div>
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    @endif
</div>
