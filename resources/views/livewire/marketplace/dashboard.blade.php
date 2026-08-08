<div>
    <x-slot:header>📊 Marketplace Analytics</x-slot:header>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="space-y-6">
        <!-- Cabeçalho -->
        <div class="flex flex-wrap justify-between items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">📊 Dashboard Marketplace</h1>
            <p class="text-gray-500 mt-1">{{ $application->name ?? 'Nenhuma aplicação selecionada' }}</p>
        </div>

        <div class="flex flex-wrap items-center gap-4">
            <!-- Seletor de Aplicação -->
            <div>
                <label for="applicationId" class="block text-xs font-medium text-gray-500 mb-1">Aplicação</label>
                <select wire:model.live="applicationId" id="applicationId"
                        class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @foreach ($applications as $app)
                        <option value="{{ $app->id }}">{{ $app->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filtro de Período -->
            <div class="flex gap-2">
                @foreach(['7' => 'Últimos 7 dias', '30' => 'Últimos 30 dias', '90' => 'Últimos 90 dias'] as $days => $label)
                    <button
                        wire:click="$set('period', '{{ $days }}')"
                        @class([
                            'px-4 py-2 rounded-lg font-medium transition',
                            'bg-blue-600 text-white' => $period === $days,
                            'bg-gray-200 text-gray-700 hover:bg-gray-300' => $period !== $days
                        ])
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    @if (!$application)
        <div class="bg-white rounded-lg shadow p-8 text-center">
            <p class="text-gray-500">Nenhuma aplicação cadastrada ainda.</p>
        </div>
    @else

    <!-- KPIs Principais -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        @php
            $kpis = [
                ['icon' => '👁️', 'label' => 'Visualizações', 'value' => $metrics['product_views'], 'color' => 'blue'],
                ['icon' => '🛒', 'label' => 'Adições ao Carrinho', 'value' => $metrics['cart_adds'], 'color' => 'green'],
                ['icon' => '💰', 'label' => 'Receita', 'value' => 'R$ ' . number_format($metrics['revenue'], 2, ',', '.'), 'color' => 'emerald'],
                ['icon' => '⭐', 'label' => 'Compras', 'value' => $metrics['purchases'], 'color' => 'purple'],
            ];
        @endphp

        @foreach($kpis as $kpi)
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-{{ $kpi['color'] }}-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">{{ $kpi['label'] }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ $kpi['value'] }}</p>
                    </div>
                    <div class="text-4xl">{{ $kpi['icon'] }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Mais KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-500 text-sm font-medium">Taxa de Conversão</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">
                {{ $metrics['product_views'] > 0 ? round(($metrics['purchases'] / $metrics['product_views']) * 100, 2) : 0 }}%
            </p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-500 text-sm font-medium">Abandono de Carrinho</p>
            <p class="text-3xl font-bold text-red-600 mt-2">{{ $metrics['cart_abandonment_rate'] }}%</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-500 text-sm font-medium">Visitantes Únicos</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $metrics['unique_visitors'] }}</p>
        </div>
    </div>

    <!-- Gráfico de Tendência -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">📈 Tendência</h2>
        <canvas id="trendChart" height="80"></canvas>
    </div>

    <!-- Vendedores e Produtos Top -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Vendedores Top -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">👥 Vendedores Top</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-gray-500 border-b">
                        <tr>
                            <th class="text-left py-2">Vendedor</th>
                            <th class="text-center">Visualizações</th>
                            <th class="text-center">Compras</th>
                            <th class="text-right">Receita</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sellers as $seller)
                            <tr class="border-b hover:bg-gray-50 cursor-pointer"
                                wire:click="$set('selectedSeller', {{ $seller['seller_id'] }})">
                                <td class="py-3">Vendedor #{{ $seller['seller_id'] }}</td>
                                <td class="text-center">{{ $seller['views'] }}</td>
                                <td class="text-center">
                                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded font-medium">
                                        {{ $seller['purchases'] }}
                                    </span>
                                </td>
                                <td class="text-right font-semibold">R$ {{ number_format($seller['revenue'], 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-gray-500">Nenhum vendedor</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Produtos Top -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">📦 Produtos Top</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-gray-500 border-b">
                        <tr>
                            <th class="text-left py-2">Produto</th>
                            <th class="text-center">Visualizações</th>
                            <th class="text-center">Compras</th>
                            <th class="text-right">Conversão</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-3">Produto #{{ $product['product_id'] }}</td>
                                <td class="text-center">{{ $product['views'] }}</td>
                                <td class="text-center">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded font-medium">
                                        {{ $product['purchases'] }}
                                    </span>
                                </td>
                                <td class="text-right font-semibold">{{ $product['conversion_rate'] }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-gray-500">Nenhum produto</td>
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
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($chartData['labels']),
                    datasets: [
                        {
                            label: 'Visualizações',
                            data: @json($chartData['views']),
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Compras',
                            data: @json($chartData['purchases']),
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
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
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>
    @endif
    </div>
</div>
