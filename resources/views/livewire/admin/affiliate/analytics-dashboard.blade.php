<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold">Affiliate Analytics</h2>
        <select
            wire:model.live="period"
            class="px-4 py-2 border rounded-lg"
        >
            <option value="7">Últimos 7 dias</option>
            <option value="30">Últimos 30 dias</option>
            <option value="90">Últimos 90 dias</option>
            <option value="365">Últimos 12 meses</option>
        </select>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Revenue -->
        <div class="bg-slate-900 rounded-lg shadow p-6 border-l-4 border-green-500">
            <p class="text-slate-400 text-sm font-medium">Receita Total</p>
            <p class="text-3xl font-bold mt-2">R$ {{ number_format($metrics['total_revenue'] ?? 0, 2, ',', '.') }}</p>
            <p class="text-xs text-slate-400 mt-2">Comissões ganhas</p>
        </div>

        <!-- Conversions -->
        <div class="bg-slate-900 rounded-lg shadow p-6 border-l-4 border-blue-500">
            <p class="text-slate-400 text-sm font-medium">Conversões</p>
            <p class="text-3xl font-bold mt-2">{{ $metrics['total_conversions'] ?? 0 }}</p>
            <p class="text-xs text-slate-400 mt-2">Vendas registradas</p>
        </div>

        <!-- Clicks -->
        <div class="bg-slate-900 rounded-lg shadow p-6 border-l-4 border-purple-500">
            <p class="text-slate-400 text-sm font-medium">Clicks Totais</p>
            <p class="text-3xl font-bold mt-2">{{ number_format($metrics['total_clicks'] ?? 0, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-400 mt-2">Links clicados</p>
        </div>

        <!-- CTR -->
        <div class="bg-slate-900 rounded-lg shadow p-6 border-l-4 border-orange-500">
            <p class="text-slate-400 text-sm font-medium">CTR</p>
            <p class="text-3xl font-bold mt-2">{{ $metrics['ctr'] ?? 0 }}%</p>
            <p class="text-xs text-slate-400 mt-2">Taxa de cliques</p>
        </div>
    </div>

    <!-- Secondary Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-slate-900 rounded-lg shadow p-6">
            <p class="text-slate-400 text-sm font-medium">EPC</p>
            <p class="text-2xl font-bold mt-2">R$ {{ number_format($metrics['epc'] ?? 0, 2, ',', '.') }}</p>
            <p class="text-xs text-slate-400 mt-2">Ganho por clique</p>
        </div>

        <div class="bg-slate-900 rounded-lg shadow p-6">
            <p class="text-slate-400 text-sm font-medium">Ticket Médio</p>
            <p class="text-2xl font-bold mt-2">R$ {{ number_format($metrics['average_order_value'] ?? 0, 2, ',', '.') }}</p>
            <p class="text-xs text-slate-400 mt-2">Por conversão</p>
        </div>

        <div class="bg-slate-900 rounded-lg shadow p-6">
            <p class="text-slate-400 text-sm font-medium">Período</p>
            <p class="text-sm font-bold mt-2">{{ $metrics['period']['start'] ?? '-' }}</p>
            <p class="text-sm font-bold">até {{ $metrics['period']['end'] ?? '-' }}</p>
        </div>
    </div>

    <!-- Top Products -->
    <div class="bg-slate-900 rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Produtos Mais Clicados</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-800 border-b">
                    <tr>
                        <th class="px-4 py-2 text-left">Produto</th>
                        <th class="px-4 py-2 text-left">Clicks</th>
                        <th class="px-4 py-2 text-left">Preço</th>
                        <th class="px-4 py-2 text-left">Comissão</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($topProducts as $product)
                        <tr class="hover:bg-slate-800">
                            <td class="px-4 py-3 font-medium">{{ $product['product_name'] }}</td>
                            <td class="px-4 py-3">{{ number_format($product['total_clicks'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3">R$ {{ number_format($product['price'], 2, ',', '.') }}</td>
                            <td class="px-4 py-3">{{ $product['commission_rate'] }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-slate-400">
                                Nenhum dado disponível
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top Content -->
    <div class="bg-slate-900 rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold mb-4">Conteúdos Mais Clicados</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-800 border-b">
                    <tr>
                        <th class="px-4 py-2 text-left">Título</th>
                        <th class="px-4 py-2 text-left">Plataforma</th>
                        <th class="px-4 py-2 text-left">Campanha</th>
                        <th class="px-4 py-2 text-left">Clicks</th>
                        <th class="px-4 py-2 text-left">Publicado</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($topContent as $content)
                        <tr class="hover:bg-slate-800">
                            <td class="px-4 py-3 font-medium">{{ Str::limit($content['title'], 30) }}</td>
                            <td class="px-4 py-3 capitalize">{{ $content['platform'] }}</td>
                            <td class="px-4 py-3 text-xs">{{ Str::limit($content['campaign_name'], 20) }}</td>
                            <td class="px-4 py-3 font-semibold">{{ number_format($content['clicks'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-xs">{{ $content['published_at'] ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-400">
                                Nenhum conteúdo publicado
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
