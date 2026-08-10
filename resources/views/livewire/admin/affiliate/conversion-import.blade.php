<div class="max-w-2xl">
    <div class="mb-6">
        <h2 class="text-2xl font-bold">Importar Conversões</h2>
    </div>

    @if (session()->has('message'))
        <div class="p-4 bg-green-100 text-green-800 rounded-lg mb-6">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="p-4 bg-red-100 text-red-800 rounded-lg mb-6">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow p-6 space-y-6">
        <form wire:submit="import" class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-2">Programa de Afiliado *</label>
                <select
                    wire:model="program_id"
                    class="w-full px-4 py-2 border rounded-lg @error('program_id') border-red-500 @enderror"
                >
                    <option value="">Selecione um programa</option>
                    @foreach ($programs as $program)
                        <option value="{{ $program->id }}">{{ $program->name }}</option>
                    @endforeach
                </select>
                @error('program_id')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Arquivo CSV *</label>
                <input
                    type="file"
                    wire:model="csv_file"
                    accept=".csv,.txt"
                    class="w-full px-4 py-2 border rounded-lg @error('csv_file') border-red-500 @enderror"
                >
                @error('csv_file')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
            </div>

            <div class="flex gap-3">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Importar
                </button>
                <a href="{{ route('admin.affiliate.conversions.index') }}" class="px-6 py-2 border rounded-lg hover:bg-gray-50">
                    Cancelar
                </a>
            </div>
        </form>

        @if (!empty($result))
            <div class="border-t pt-6">
                <h3 class="text-lg font-semibold mb-4">Resultado da Importação</h3>
                <div class="space-y-2">
                    <p class="text-green-600">✓ Registros importados com sucesso: <strong>{{ $result['successful'] }}</strong></p>
                    @if ($result['failed'] > 0)
                        <p class="text-red-600">✗ Registros com erro: <strong>{{ $result['failed'] }}</strong></p>
                        @if (!empty($result['errors']))
                            <div class="bg-red-50 p-4 rounded mt-4">
                                <p class="font-medium mb-2">Detalhes dos erros:</p>
                                <ul class="space-y-1 text-sm">
                                    @foreach ($result['errors'] as $error)
                                        <li class="text-red-700">• {{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        @endif

        <div class="border-t pt-6">
            <h3 class="text-lg font-semibold mb-4">Formato do CSV</h3>
            <p class="text-sm text-gray-600 mb-4">O arquivo deve conter as seguintes colunas (cabeçalho obrigatório):</p>
            <div class="bg-gray-50 p-4 rounded overflow-x-auto">
                <code class="text-xs">order_reference,product_name,order_date,product_price,commission_rate,commission_value,campaign_name,notes</code>
            </div>
            <div class="mt-4 space-y-2">
                <p class="text-sm text-gray-700"><strong>order_reference</strong> — Identificador único do pedido (ex: PED-001)</p>
                <p class="text-sm text-gray-700"><strong>product_name</strong> — Nome do produto (buscado por similaridade)</p>
                <p class="text-sm text-gray-700"><strong>order_date</strong> — Data do pedido (formato: YYYY-MM-DD)</p>
                <p class="text-sm text-gray-700"><strong>product_price</strong> — Preço do produto</p>
                <p class="text-sm text-gray-700"><strong>commission_rate</strong> — Taxa de comissão em %</p>
                <p class="text-sm text-gray-700"><strong>commission_value</strong> — Valor da comissão</p>
                <p class="text-sm text-gray-700"><strong>campaign_name</strong> — Nome da campanha (opcional, buscado por nome exato)</p>
                <p class="text-sm text-gray-700"><strong>notes</strong> — Notas adicionais (opcional)</p>
            </div>
        </div>
    </div>
</div>
