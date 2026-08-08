<div>
    <x-slot:header>📊 Marketplace Analytics</x-slot:header>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="space-y-6">
        <!-- Cabeçalho -->
        <div class="flex flex-wrap justify-between items-center gap-4">
            <div>
                <p class="text-sm text-slate-500 mt-1">{{ $application->name ?? 'Nenhuma aplicação selecionada' }}</p>
            </div>

            <div class="flex flex-wrap items-end gap-4">
                <!-- Seletor de Aplicação -->
                <div>
                    <label for="applicationId" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Aplicação</label>
                    <select wire:model.live="applicationId" id="applicationId"
                            class="rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                        @foreach ($applications as $app)
                            <option value="{{ $app->id }}">{{ $app->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro de Período -->
                <div class="flex gap-2">
                    @foreach(['7' => '7 dias', '30' => '30 dias', '90' => '90 dias'] as $days => $label)
                        <button
                            wire:click="$set('period', '{{ $days }}')"
                            @class([
                                'px-3 py-2 rounded-lg text-sm font-medium transition',
                                'bg-amber-400 text-slate-950' => $period === $days,
                                'bg-slate-800 text-slate-400 hover:bg-slate-700 hover:text-white' => $period !== $days,
                            ])
                        >
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        @if (!$application)
            <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-8 text-center">
                <p class="text-sm text-slate-500">Nenhuma aplicação cadastrada ainda.</p>
            </div>
        @else
            <!-- KPIs Principais -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                @php
                    $kpis = [
                        ['icon' => '👁️', 'label' => 'Visualizações', 'value' => $metrics['product_views'], 'accent' => 'border-l-blue-400'],
                        ['icon' => '🛒', 'label' => 'Adições ao Carrinho', 'value' => $metrics['cart_adds'], 'accent' => 'border-l-emerald-400'],
                        ['icon' => '💰', 'label' => 'Receita', 'value' => 'R$ ' . number_format($metrics['revenue'], 2, ',', '.'), 'accent' => 'border-l-amber-400'],
                        ['icon' => '⭐', 'label' => 'Compras', 'value' => $metrics['purchases'], 'accent' => 'border-l-purple-400'],
                    ];
                @endphp

                @foreach($kpis as $kpi)
                    <div class="rounded-xl border border-slate-800 {{ $kpi['accent'] }} border-l-4 bg-slate-900/60 p-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ $kpi['label'] }}</p>
                                <p class="text-2xl font-semibold text-white mt-2">{{ $kpi['value'] }}</p>
                            </div>
                            <div class="text-3xl">{{ $kpi['icon'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Mais KPIs -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Taxa de Conversão</p>
                    <p class="text-2xl font-semibold text-white mt-2">
                        {{ $metrics['product_views'] > 0 ? round(($metrics['purchases'] / $metrics['product_views']) * 100, 2) : 0 }}%
                    </p>
                </div>

                <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Abandono de Carrinho</p>
                    <p class="text-2xl font-semibold text-red-400 mt-2">{{ $metrics['cart_abandonment_rate'] }}%</p>
                </div>

                <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Visitantes Únicos</p>
                    <p class="text-2xl font-semibold text-white mt-2">{{ $metrics['unique_visitors'] }}</p>
                </div>
            </div>

            <!-- Gráfico de Tendência -->
            <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5">
                <h3 class="text-xs font-medium uppercase tracking-wide text-slate-500 mb-4">📈 Tendência</h3>
                <canvas id="trendChart" height="80"></canvas>
            </div>

            <!-- Vendedores e Produtos Top -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Vendedores Top -->
                <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5">
                    <h3 class="text-xs font-medium uppercase tracking-wide text-slate-500 mb-3">👥 Vendedores Top</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="text-slate-500 border-b border-slate-800">
                                <tr>
                                    <th class="text-left py-2 font-medium">Vendedor</th>
                                    <th class="text-center font-medium">Visualizações</th>
                                    <th class="text-center font-medium">Compras</th>
                                    <th class="text-right font-medium">Receita</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sellers as $seller)
                                    <tr class="border-b border-slate-800/60 hover:bg-slate-800/40 cursor-pointer transition"
                                        wire:click="$set('selectedSeller', {{ $seller['seller_id'] }})">
                                        <td class="py-3 text-slate-300">Vendedor #{{ $seller['seller_id'] }}</td>
                                        <td class="text-center text-slate-300">{{ $seller['views'] }}</td>
                                        <td class="text-center">
                                            <span class="px-2 py-0.5 rounded-full bg-emerald-400/10 text-emerald-400 font-medium">
                                                {{ $seller['purchases'] }}
                                            </span>
                                        </td>
                                        <td class="text-right font-semibold text-white">R$ {{ number_format($seller['revenue'], 2, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-slate-500">Nenhum vendedor</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Produtos Top -->
                <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5">
                    <h3 class="text-xs font-medium uppercase tracking-wide text-slate-500 mb-3">📦 Produtos Top</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="text-slate-500 border-b border-slate-800">
                                <tr>
                                    <th class="text-left py-2 font-medium">Produto</th>
                                    <th class="text-center font-medium">Visualizações</th>
                                    <th class="text-center font-medium">Compras</th>
                                    <th class="text-right font-medium">Conversão</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $product)
                                    <tr class="border-b border-slate-800/60 hover:bg-slate-800/40 transition">
                                        <td class="py-3 text-slate-300">Produto #{{ $product['product_id'] }}</td>
                                        <td class="text-center text-slate-300">{{ $product['views'] }}</td>
                                        <td class="text-center">
                                            <span class="px-2 py-0.5 rounded-full bg-blue-400/10 text-blue-400 font-medium">
                                                {{ $product['purchases'] }}
                                            </span>
                                        </td>
                                        <td class="text-right font-semibold text-white">{{ $product['conversion_rate'] }}%</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-slate-500">Nenhum produto</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Script Chart.js -->
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const ctx = document.getElementById('trendChart').getContext('2d');
                    const gridColor = 'rgba(148, 163, 184, 0.1)';
                    const tickColor = '#94a3b8';

                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: @json($chartData['labels']),
                            datasets: [
                                {
                                    label: 'Visualizações',
                                    data: @json($chartData['views']),
                                    borderColor: '#60a5fa',
                                    backgroundColor: 'rgba(96, 165, 250, 0.1)',
                                    tension: 0.4,
                                    fill: true
                                },
                                {
                                    label: 'Compras',
                                    data: @json($chartData['purchases']),
                                    borderColor: '#34d399',
                                    backgroundColor: 'rgba(52, 211, 153, 0.1)',
                                    tension: 0.4,
                                    fill: true
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top',
                                    labels: { color: tickColor }
                                }
                            },
                            scales: {
                                x: {
                                    ticks: { color: tickColor },
                                    grid: { color: gridColor }
                                },
                                y: {
                                    beginAtZero: true,
                                    ticks: { color: tickColor },
                                    grid: { color: gridColor }
                                }
                            }
                        }
                    });
                });
            </script>
        @endif
    </div>
</div>
