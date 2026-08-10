<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-white">🎯 Recomendação JMF</h1>
        <button wire:click="loadRecommendations" class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700">
            🔄 Atualizar
        </button>
    </div>

    @if (empty($recommendations))
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
            <p class="text-yellow-300">Nenhuma recomendação disponível. Verifique se as Fases 26 e 29 possuem dados.</p>
        </div>
    @else
        <div class="grid grid-cols-1 gap-6">
            @foreach ($recommendations as $rec)
                <div class="bg-slate-900 rounded-lg shadow p-6 border-l-4 border-blue-500 hover:shadow-lg transition">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="text-xl font-bold text-white">{{ $rec['product_name'] }}</h3>
                            <p class="text-sm text-slate-400">Tendência: {{ $rec['trend_term'] }}</p>
                        </div>
                        <div class="text-right">
                            <div class="text-3xl font-bold text-blue-600">{{ $rec['confidence_score'] }}</div>
                            <p class="text-xs text-slate-400">Confiança</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4 mb-4">
                        <div class="bg-slate-800 rounded p-3">
                            <p class="text-xs text-slate-400">Trend Score</p>
                            <p class="text-lg font-bold text-white">{{ $rec['trend_score'] }}</p>
                        </div>
                        <div class="bg-slate-800 rounded p-3">
                            <p class="text-xs text-slate-400">Oportunidade</p>
                            <p class="text-lg font-bold text-white">{{ $rec['opportunity_score'] }}</p>
                        </div>
                        <div class="bg-slate-800 rounded p-3">
                            <p class="text-xs text-slate-400">Desempenho</p>
                            <p class="text-lg font-bold text-white">{{ $rec['performance_score'] }}</p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <p class="text-sm font-semibold text-white">Motivos:</p>
                        <ul class="space-y-1">
                            @foreach ($rec['reasons'] as $reason)
                                <li class="text-sm text-slate-300 flex items-center">
                                    <span class="text-green-500 mr-2">✓</span> {{ $reason }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
