<div class="max-w-2xl">
    <div class="mb-6">
        <h2 class="text-2xl font-bold">
            {{ $conversion ? 'Editar Conversão' : 'Nova Conversão' }}
        </h2>
    </div>

    @if (session()->has('message'))
        <div class="p-4 bg-green-100 text-green-800 rounded-lg mb-6">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-6 bg-white rounded-lg shadow p-6">
        <div>
            <label class="block text-sm font-medium mb-2">Programa de Afiliado *</label>
            <select
                wire:model="affiliate_program_id"
                class="w-full px-4 py-2 border rounded-lg @error('affiliate_program_id') border-red-500 @enderror"
            >
                <option value="">Selecione um programa</option>
                @foreach ($programs as $program)
                    <option value="{{ $program->id }}">{{ $program->name }}</option>
                @endforeach
            </select>
            @error('affiliate_program_id')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">Produto *</label>
            <select
                wire:model="affiliate_product_id"
                class="w-full px-4 py-2 border rounded-lg @error('affiliate_product_id') border-red-500 @enderror"
            >
                <option value="">Selecione um produto</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}">{{ $product->product_name }}</option>
                @endforeach
            </select>
            @error('affiliate_product_id')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">Campanha (opcional)</label>
            <select
                wire:model="campaign_id"
                class="w-full px-4 py-2 border rounded-lg"
            >
                <option value="">Nenhuma campanha</option>
                @foreach ($campaigns as $campaign)
                    <option value="{{ $campaign->id }}">{{ $campaign->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">Referência do Pedido *</label>
            <input
                type="text"
                wire:model="order_reference"
                class="w-full px-4 py-2 border rounded-lg @error('order_reference') border-red-500 @enderror"
                placeholder="Ex: PED-001"
                {{ $conversion ? 'disabled' : '' }}
            >
            @error('order_reference')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">Data do Pedido *</label>
            <input
                type="date"
                wire:model="order_date"
                class="w-full px-4 py-2 border rounded-lg @error('order_date') border-red-500 @enderror"
            >
            @error('order_date')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
        </div>

        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2">Preço do Produto *</label>
                <input
                    type="number"
                    wire:model="product_price"
                    step="0.01"
                    class="w-full px-4 py-2 border rounded-lg @error('product_price') border-red-500 @enderror"
                    placeholder="0.00"
                >
                @error('product_price')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Taxa de Comissão (%) *</label>
                <input
                    type="number"
                    wire:model="commission_rate"
                    step="0.01"
                    min="0"
                    max="100"
                    class="w-full px-4 py-2 border rounded-lg @error('commission_rate') border-red-500 @enderror"
                    placeholder="0.00"
                >
                @error('commission_rate')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Valor de Comissão *</label>
                <input
                    type="number"
                    wire:model="commission_value"
                    step="0.01"
                    class="w-full px-4 py-2 border rounded-lg @error('commission_value') border-red-500 @enderror"
                    placeholder="0.00"
                >
                @error('commission_value')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">Status *</label>
            <select
                wire:model="status"
                class="w-full px-4 py-2 border rounded-lg @error('status') border-red-500 @enderror"
            >
                <option value="pending">Pendente</option>
                <option value="approved">Aprovado</option>
                <option value="paid">Pago</option>
                <option value="cancelled">Cancelado</option>
            </select>
            @error('status')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">Notas</label>
            <textarea
                wire:model="notes"
                class="w-full px-4 py-2 border rounded-lg"
                rows="3"
                placeholder="Notas adicionais sobre a conversão"
            ></textarea>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                {{ $conversion ? 'Atualizar' : 'Registrar' }} Conversão
            </button>
            <a href="{{ route('admin.affiliate.conversions.index') }}" class="px-6 py-2 border rounded-lg hover:bg-gray-50">
                Cancelar
            </a>
        </div>
    </form>
</div>
