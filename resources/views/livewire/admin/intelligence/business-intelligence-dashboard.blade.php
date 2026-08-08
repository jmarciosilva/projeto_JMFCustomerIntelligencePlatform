<div>
    <x-slot:header>Business Intelligence</x-slot:header>

    <x-slot:help>
        <x-help-modal title="Ajuda — Business Intelligence">
            <p>Painel de inteligência artificial da <strong>Fase 13</strong>: segmentação automática de clientes (RFV), tendências de produtos, previsão de vendas e oportunidades comerciais detectadas.</p>
            <p>Os dados são recalculados diariamente pelos comandos agendados <code>intelligence:compute-segments</code>, <code>intelligence:analyze-trends</code> e <code>intelligence:detect-opportunities</code>.</p>
        </x-help-modal>
    </x-slot:help>

    <div class="flex flex-wrap items-center gap-4 mb-8">
        <div>
            <label for="applicationId" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Aplicação</label>
            <select wire:model.live="applicationId" id="applicationId"
                    class="rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                @foreach ($applications as $app)
                    <option value="{{ $app->id }}">{{ $app->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    @if (!$application)
        <p class="text-sm text-slate-500">Nenhuma aplicação cadastrada ainda.</p>
    @else
        {{-- Segmentação de Clientes --}}
        <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5 mb-8">
            <h3 class="text-xs font-medium uppercase tracking-wide text-slate-500 mb-4">Segmentação de clientes (RFV)</h3>

            <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                @foreach ($segments as $segment)
                    @php
                        $barClass = match ($segment['key']) {
                            'vip' => 'bg-amber-400',
                            'engaged' => 'bg-blue-400',
                            'converted' => 'bg-emerald-400',
                            'new' => 'bg-cyan-400',
                            default => 'bg-slate-500',
                        };
                    @endphp
                    <div class="rounded-lg border border-slate-800 bg-slate-950/60 p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-lg">{{ $segment['icon'] }}</span>
                            <span class="text-xs font-mono text-slate-500">{{ $segment['percentage'] }}%</span>
                        </div>
                        <dd class="text-2xl font-semibold text-white">{{ $segment['count'] }}</dd>
                        <dt class="text-xs text-slate-400 mt-0.5">{{ $segment['label'] }}</dt>
                        <div class="h-1.5 rounded-full bg-slate-800 overflow-hidden mt-3">
                            <div class="h-full rounded-full {{ $barClass }}" style="width: {{ $segment['percentage'] }}%"></div>
                        </div>
                        @if ($segment['count'] > 0)
                            <p class="text-[11px] text-slate-500 mt-2">Score médio: {{ $segment['avg_score'] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2 mb-8">
            {{-- Produtos em Alta --}}
            <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5">
                <h3 class="text-xs font-medium uppercase tracking-wide text-slate-500 mb-3">📈 Produtos em alta</h3>
                <ul class="space-y-3">
                    @forelse ($risingProducts as $trend)
                        <li class="flex items-center justify-between gap-2">
                            <span class="text-sm text-slate-300">Produto #{{ $trend->product_id }}</span>
                            <span class="text-xs font-mono font-semibold text-emerald-400">+{{ $trend->growth_rate }}%</span>
                        </li>
                    @empty
                        <li class="text-sm text-slate-500">Nenhum produto em alta no momento.</li>
                    @endforelse
                </ul>
            </div>

            {{-- Produtos em Queda --}}
            <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5">
                <h3 class="text-xs font-medium uppercase tracking-wide text-slate-500 mb-3">📉 Produtos em queda</h3>
                <ul class="space-y-3">
                    @forelse ($fallingProducts as $trend)
                        <li class="flex items-center justify-between gap-2">
                            <span class="text-sm text-slate-300">Produto #{{ $trend->product_id }}</span>
                            <span class="text-xs font-mono font-semibold text-red-400">{{ $trend->growth_rate }}%</span>
                        </li>
                    @empty
                        <li class="text-sm text-slate-500">Nenhum produto em queda no momento.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        {{-- Previsão de Vendas --}}
        <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5 mb-8">
            <h3 class="text-xs font-medium uppercase tracking-wide text-slate-500 mb-4">🔮 Previsão de vendas</h3>

            @if ($forecasts->isEmpty())
                <p class="text-sm text-slate-500">Sem dados suficientes para gerar previsão.</p>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach ($forecasts as $forecast)
                        @php
                            $confidenceClasses = match ($forecast->confidence) {
                                'high' => 'bg-emerald-400/10 text-emerald-400',
                                'medium' => 'bg-amber-400/10 text-amber-400',
                                default => 'bg-slate-700/50 text-slate-400',
                            };
                        @endphp
                        <div class="rounded-lg border border-slate-800 bg-slate-950/60 p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs uppercase tracking-wide text-slate-500">Próximos {{ $forecast->horizon_days }} dias</span>
                                <span class="text-[10px] font-medium uppercase px-2 py-0.5 rounded-full {{ $confidenceClasses }}">
                                    Confiança {{ $forecast->confidence }}
                                </span>
                            </div>
                            <dd class="text-2xl font-semibold text-white">R$ {{ number_format($forecast->predicted_revenue, 2, ',', '.') }}</dd>
                            <dt class="text-xs text-slate-400 mt-0.5">{{ $forecast->predicted_purchases }} compras previstas</dt>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Oportunidades Comerciais --}}
        <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xs font-medium uppercase tracking-wide text-slate-500">💡 Oportunidades comerciais</h3>
                <div class="flex gap-2 text-[11px]">
                    @php
                        $typeLabels = ['cross_sell' => 'Cross-sell', 'bundle' => 'Bundle', 'up_sell' => 'Up-sell', 'win_back' => 'Win-back'];
                    @endphp
                    @foreach ($typeLabels as $key => $label)
                        <span class="px-2 py-1 rounded-full bg-slate-800 text-slate-400">{{ $label }}: {{ $opportunityCounts[$key] ?? 0 }}</span>
                    @endforeach
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-slate-500 border-b border-slate-800">
                        <tr>
                            <th class="text-left py-2 font-medium">Tipo</th>
                            <th class="text-left font-medium">Detalhe</th>
                            <th class="text-right font-medium">Score</th>
                            <th class="text-right font-medium">Valor potencial</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($opportunities as $opportunity)
                            @php
                                $typeClasses = match ($opportunity->type) {
                                    'cross_sell' => 'bg-blue-400/10 text-blue-400',
                                    'bundle' => 'bg-purple-400/10 text-purple-400',
                                    'up_sell' => 'bg-emerald-400/10 text-emerald-400',
                                    'win_back' => 'bg-amber-400/10 text-amber-400',
                                    default => 'bg-slate-700/50 text-slate-400',
                                };
                            @endphp
                            <tr class="border-b border-slate-800/60">
                                <td class="py-3">
                                    <span class="text-[11px] font-medium uppercase px-2 py-0.5 rounded-full {{ $typeClasses }}">
                                        {{ $typeLabels[$opportunity->type] ?? $opportunity->type }}
                                    </span>
                                </td>
                                <td class="text-slate-300">{{ $opportunity->reason }}</td>
                                <td class="text-right font-mono text-slate-300">{{ $opportunity->score }}</td>
                                <td class="text-right font-mono text-slate-300">
                                    {{ $opportunity->potential_value ? 'R$ '.number_format($opportunity->potential_value, 2, ',', '.') : '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-slate-500">Nenhuma oportunidade detectada.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
