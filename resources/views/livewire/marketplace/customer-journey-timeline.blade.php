<div>
    <x-slot:header>🎯 Jornada do Comprador</x-slot:header>

    <div class="space-y-6">
        <!-- Cabeçalho do Contato -->
        <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">👤 {{ $contact->name ?? 'Contato' }}</h1>
                <p class="text-gray-500 mt-1">{{ $contact->email }}</p>
            </div>

            <!-- Badge de Status -->
            <div>
                @if ($conversionStatus === 'converted')
                    <span class="px-4 py-2 bg-green-100 text-green-700 rounded-lg font-semibold flex items-center gap-2">
                        ✅ Cliente Convertido
                    </span>
                @elseif ($conversionStatus === 'abandoned')
                    <span class="px-4 py-2 bg-orange-100 text-orange-700 rounded-lg font-semibold flex items-center gap-2">
                        ⏸️ Carrinho Abandonado
                    </span>
                @else
                    <span class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg font-semibold flex items-center gap-2">
                        👁️ Em Navegação
                    </span>
                @endif
            </div>
        </div>

        <!-- Info Cards -->
        <div class="grid grid-cols-3 gap-4 mt-6">
            <div class="bg-gray-50 p-4 rounded">
                <p class="text-gray-500 text-sm">Total de Eventos</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ count($journey) }}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded">
                <p class="text-gray-500 text-sm">Lead Score</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $contact->lead_score ?? 0 }} pts</p>
            </div>
            <div class="bg-gray-50 p-4 rounded">
                <p class="text-gray-500 text-sm">Última Atividade</p>
                <p class="text-sm font-bold text-gray-900 mt-1">
                    {{ count($journey) > 0 ? \Carbon\Carbon::createFromTimestamp($journey[count($journey)-1]['timestamp'])->diffForHumans() : 'N/A' }}
                </p>
            </div>
        </div>
    </div>

    <!-- Stages da Jornada -->
    @if (count($stages) > 0)
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">🎯 Estágios da Jornada</h2>
            <div class="flex gap-2 flex-wrap">
                @foreach ($stages as $stage)
                    <span class="px-4 py-2 bg-blue-100 text-blue-700 rounded-full font-medium flex items-center gap-2">
                        ✓ {{ $stage['name'] }}
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Timeline de Eventos -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-6">📅 Timeline de Eventos</h2>

        @if (count($journey) > 0)
            <div class="space-y-4">
                @foreach ($journey as $index => $event)
                    <div class="flex gap-4">
                        <!-- Timeline Line -->
                        <div class="flex flex-col items-center">
                            <div class="w-12 h-12 rounded-full bg-{{ $event['color'] }}-100 flex items-center justify-center text-lg font-bold text-{{ $event['color'] }}-700 ring-2 ring-{{ $event['color'] }}-200">
                                {{ $event['icon'] }}
                            </div>
                            @if ($index < count($journey) - 1)
                                <div class="w-1 h-8 bg-gray-200 mt-2"></div>
                            @endif
                        </div>

                        <!-- Event Info -->
                        <div class="flex-1 pt-2">
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="font-bold text-gray-900">{{ $event['display_name'] }}</h3>
                                        <p class="text-sm text-gray-500 mt-1">{{ $event['occurred_at'] }}</p>
                                    </div>
                                    <span class="text-xs font-semibold px-2 py-1 bg-{{ $event['color'] }}-100 text-{{ $event['color'] }}-700 rounded">
                                        {{ $event['event_name'] }}
                                    </span>
                                </div>

                                <!-- Event Details -->
                                @if ($event['product_id'])
                                    <p class="text-sm text-gray-600 mt-2">📦 Produto #{{ $event['product_id'] }}</p>
                                @endif

                                @if ($event['seller_id'])
                                    <p class="text-sm text-gray-600">👥 Vendedor #{{ $event['seller_id'] }}</p>
                                @endif

                                @if ($event['value'])
                                    <p class="text-sm font-semibold text-green-600 mt-2">
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
                <p class="text-gray-500 text-lg">Nenhum evento registrado para este contato</p>
            </div>
        @endif
    </div>

    <!-- Recomendações -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg shadow p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">💡 Recomendações</h2>

        @if ($conversionStatus === 'abandoned')
            <div class="space-y-2 text-gray-700">
                <p>• ⏸️ Este cliente tem carrinho abandonado — considere enviar recall</p>
                <p>• 🎯 Adicione desconto ou frete grátis para recuperar venda</p>
                <p>• 📧 Envie e-mail com produtos semelhantes</p>
            </div>
        @elseif ($conversionStatus === 'converted')
            <div class="space-y-2 text-gray-700">
                <p>• ✅ Cliente convertido — priorize retenção</p>
                <p>• 🎁 Ofereça produtos relacionados ou complementares</p>
                <p>• 👑 Considere programa de lealdade ou VIP</p>
                <p>• ⭐ Peça avaliação e feedback</p>
            </div>
        @else
            <div class="space-y-2 text-gray-700">
                <p>• 👁️ Cliente em exploração — continue nurturando</p>
                <p>• 🎯 Mostre produtos similares aos visitados</p>
                <p>• 💬 Ofereça suporte ou chatbot para dúvidas</p>
            </div>
        @endif
    </div>
    </div>
</div>

<style>
    /* Tailwind colors para timeline */
    .ring-2 { border: 2px solid; }
</style>
