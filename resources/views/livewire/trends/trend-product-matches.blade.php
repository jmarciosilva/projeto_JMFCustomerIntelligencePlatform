<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
            Produtos Relacionados
        </h3>
        <span class="text-sm text-gray-500 dark:text-gray-400">
            {{ $matches->total() }} produto(s)
        </span>
    </div>

    @if($matches->count() > 0)
        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300">
                            Produto
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300">
                            Programa
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 dark:text-gray-300">
                            Score de Match
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 dark:text-gray-300">
                            Preço
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 dark:text-gray-300">
                            Comissão
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($matches as $match)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                <div class="font-medium">{{ $match->product->name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $match->product->category }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                {{ $match->product->affiliateProgram->name }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex flex-col items-end space-y-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($match->match_score >= 75)
                                            bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                        @elseif($match->match_score >= 50)
                                            bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                        @else
                                            bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                        @endif
                                    ">
                                        {{ number_format($match->match_score, 1) }}%
                                    </span>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 space-y-0.5">
                                        @if($match->match_breakdown)
                                            <div>
                                                K: {{ number_format($match->match_breakdown['keyword'], 0) }}%
                                            </div>
                                            <div>
                                                C: {{ number_format($match->match_breakdown['category'] ?? 0, 0) }}%
                                            </div>
                                            <div>
                                                B: {{ number_format($match->match_breakdown['brand'] ?? 0, 0) }}%
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right text-sm text-gray-900 dark:text-gray-100">
                                R$ {{ number_format($match->product->price, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right text-sm font-medium text-green-600 dark:text-green-400">
                                {{ number_format($match->product->commission_percentage, 1) }}%
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        <div class="flex justify-center">
            {{ $matches->links() }}
        </div>
    @else
        <div class="rounded-lg bg-gray-50 dark:bg-gray-800 p-4 text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Nenhum produto encontrado. Execute a coleta de trends e o cálculo de scores para gerar matches.
            </p>
        </div>
    @endif
</div>
