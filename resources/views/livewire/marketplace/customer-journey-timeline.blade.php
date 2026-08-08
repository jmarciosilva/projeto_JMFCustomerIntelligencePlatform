<div>
    <x-slot:header>🎯 Jornada do Comprador</x-slot:header>

    <div class="space-y-6">
        <!-- Cabeçalho do Contato -->
        <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-white">👤 {{ $contact->name ?? 'Contato' }}</h1>
                    <p class="text-sm text-slate-500 mt-1">{{ $contact->email }}</p>
                </div>

                <!-- Badge de Status -->
                <div>
                    @if ($conversionStatus === 'converted')
                        <span class="px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 bg-emerald-400/10 text-emerald-400">
                            ✅ Cliente Convertido
                        </span>
                    @elseif ($conversionStatus === 'abandoned')
                        <span class="px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 bg-amber-400/10 text-amber-400">
                            ⏸️ Carrinho Abandonado
                        </span>
                    @else
                        <span class="px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 bg-blue-400/10 text-blue-400">
                            👁️ Em Navegação
                        </span>
                    @endif
                </div>
            </div>

            <!-- Info Cards -->
            <div class="grid grid-cols-3 gap-4 mt-6">
                <div class="rounded-lg border border-slate-800 bg-slate-950/60 p-4">
                    <p class="text-xs text-slate-500">Total de Eventos</p>
                    <p class="text-xl font-semibold text-white mt-1">{{ count($journey) }}</p>
                </div>
                <div class="rounded-lg border border-slate-800 bg-slate-950/60 p-4">
                    <p class="text-xs text-slate-500">Lead Score</p>
                    <p class="text-xl font-semibold text-white mt-1">{{ $contact->lead_score ?? 0 }} pts</p>
                </div>
                <div class="rounded-lg border border-slate-800 bg-slate-950/60 p-4">
                    <p class="text-xs text-slate-500">Última Atividade</p>
                    <p class="text-sm font-semibold text-white mt-1">
                        {{ count($journey) > 0 ? \Carbon\Carbon::createFromTimestamp($journey[count($journey)-1]['timestamp'])->diffForHumans() : 'N/A' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Stages da Jornada -->
        @if (count($stages) > 0)
            <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5">
                <h3 class="text-xs font-medium uppercase tracking-wide text-slate-500 mb-3">🎯 Estágios da Jornada</h3>
                <div class="flex gap-2 flex-wrap">
                    @foreach ($stages as $stage)
                        <span class="px-4 py-2 rounded-full text-sm font-medium flex items-center gap-2 bg-blue-400/10 text-blue-400">
                            ✓ {{ $stage['name'] }}
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Timeline de Eventos -->
        <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5">
            <h3 class="text-xs font-medium uppercase tracking-wide text-slate-500 mb-5">📅 Timeline de Eventos</h3>

            @if (count($journey) > 0)
                <div class="space-y-4">
                    @foreach ($journey as $index => $event)
                        @php
                            $colorClasses = match ($event['color']) {
                                'blue' => ['bg' => 'bg-blue-400/10', 'text' => 'text-blue-400', 'ring' => 'ring-blue-400/30'],
                                'red' => ['bg' => 'bg-red-400/10', 'text' => 'text-red-400', 'ring' => 'ring-red-400/30'],
                                'gray' => ['bg' => 'bg-slate-700/50', 'text' => 'text-slate-400', 'ring' => 'ring-slate-600/40'],
                                'green' => ['bg' => 'bg-green-400/10', 'text' => 'text-green-400', 'ring' => 'ring-green-400/30'],
                                'yellow' => ['bg' => 'bg-yellow-400/10', 'text' => 'text-yellow-400', 'ring' => 'ring-yellow-400/30'],
                                'orange' => ['bg' => 'bg-orange-400/10', 'text' => 'text-orange-400', 'ring' => 'ring-orange-400/30'],
                                'purple' => ['bg' => 'bg-purple-400/10', 'text' => 'text-purple-400', 'ring' => 'ring-purple-400/30'],
                                'emerald' => ['bg' => 'bg-emerald-400/10', 'text' => 'text-emerald-400', 'ring' => 'ring-emerald-400/30'],
                                'amber' => ['bg' => 'bg-amber-400/10', 'text' => 'text-amber-400', 'ring' => 'ring-amber-400/30'],
                                'indigo' => ['bg' => 'bg-indigo-400/10', 'text' => 'text-indigo-400', 'ring' => 'ring-indigo-400/30'],
                                'cyan' => ['bg' => 'bg-cyan-400/10', 'text' => 'text-cyan-400', 'ring' => 'ring-cyan-400/30'],
                                default => ['bg' => 'bg-slate-700/50', 'text' => 'text-slate-400', 'ring' => 'ring-slate-600/40'],
                            };
                        @endphp
                        <div class="flex gap-4">
                            <!-- Timeline Line -->
                            <div class="flex flex-col items-center">
                                <div class="w-12 h-12 rounded-full flex items-center justify-center text-lg font-bold ring-2 {{ $colorClasses['bg'] }} {{ $colorClasses['text'] }} {{ $colorClasses['ring'] }}">
                                    {{ $event['icon'] }}
                                </div>
                                @if ($index < count($journey) - 1)
                                    <div class="w-px h-8 bg-slate-800 mt-2"></div>
                                @endif
                            </div>

                            <!-- Event Info -->
                            <div class="flex-1 pt-2">
                                <div class="rounded-lg border border-slate-800 bg-slate-950/60 p-4">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h4 class="font-semibold text-white">{{ $event['display_name'] }}</h4>
                                            <p class="text-xs text-slate-500 mt-1">{{ $event['occurred_at'] }}</p>
                                        </div>
                                        <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $colorClasses['bg'] }} {{ $colorClasses['text'] }}">
                                            {{ $event['event_name'] }}
                                        </span>
                                    </div>

                                    <!-- Event Details -->
                                    @if ($event['product_id'])
                                        <p class="text-sm text-slate-400 mt-2">📦 Produto #{{ $event['product_id'] }}</p>
                                    @endif

                                    @if ($event['seller_id'])
                                        <p class="text-sm text-slate-400">👥 Vendedor #{{ $event['seller_id'] }}</p>
                                    @endif

                                    @if ($event['value'])
                                        <p class="text-sm font-semibold text-emerald-400 mt-2">
                                            💰 R$ {{ number_format($event['value'], 2, ',', '.') }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <p class="text-slate-500">Nenhum evento registrado para este contato</p>
                </div>
            @endif
        </div>

        <!-- Recomendações -->
        <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5">
            <h3 class="text-xs font-medium uppercase tracking-wide text-slate-500 mb-4">💡 Recomendações</h3>

            @if ($conversionStatus === 'abandoned')
                <div class="space-y-2 text-sm text-slate-300">
                    <p>• ⏸️ Este cliente tem carrinho abandonado — considere enviar recall</p>
                    <p>• 🎯 Adicione desconto ou frete grátis para recuperar venda</p>
                    <p>• 📧 Envie e-mail com produtos semelhantes</p>
                </div>
            @elseif ($conversionStatus === 'converted')
                <div class="space-y-2 text-sm text-slate-300">
                    <p>• ✅ Cliente convertido — priorize retenção</p>
                    <p>• 🎁 Ofereça produtos relacionados ou complementares</p>
                    <p>• 👑 Considere programa de lealdade ou VIP</p>
                    <p>• ⭐ Peça avaliação e feedback</p>
                </div>
            @else
                <div class="space-y-2 text-sm text-slate-300">
                    <p>• 👁️ Cliente em exploração — continue nurturando</p>
                    <p>• 🎯 Mostre produtos similares aos visitados</p>
                    <p>• 💬 Ofereça suporte ou chatbot para dúvidas</p>
                </div>
            @endif
        </div>
    </div>
</div>
