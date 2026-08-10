<div class="max-w-2xl">
    <div class="mb-6">
        <h2 class="text-2xl font-bold">
            {{ $campaign ? 'Editar Campanha' : 'Nova Campanha' }}
        </h2>
    </div>

    @if (session()->has('message'))
        <div class="p-4 bg-emerald-500/20 text-emerald-300 rounded-lg mb-6">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-6 bg-slate-900 rounded-lg shadow p-6">
        <div>
            <label class="block text-sm font-medium mb-2">Nome da Campanha</label>
            <input
                type="text"
                wire:model="name"
                class="w-full px-4 py-2 border rounded-lg @error('name') border-red-500 @enderror"
                placeholder="Ex: Black Friday 2024"
            >
            @error('name')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">Descrição</label>
            <textarea
                wire:model="description"
                class="w-full px-4 py-2 border rounded-lg @error('description') border-red-500 @enderror"
                rows="4"
                placeholder="Descreva os objetivos da campanha"
            ></textarea>
            @error('description')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2">Data Inicial</label>
                <input
                    type="date"
                    wire:model="start_date"
                    class="w-full px-4 py-2 border rounded-lg @error('start_date') border-red-500 @enderror"
                >
                @error('start_date')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Data Final</label>
                <input
                    type="date"
                    wire:model="end_date"
                    class="w-full px-4 py-2 border rounded-lg @error('end_date') border-red-500 @enderror"
                >
                @error('end_date')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2">Clicks Esperados</label>
                <input
                    type="number"
                    wire:model="expected_clicks"
                    class="w-full px-4 py-2 border rounded-lg @error('expected_clicks') border-red-500 @enderror"
                    min="0"
                >
                @error('expected_clicks')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Conversões Esperadas</label>
                <input
                    type="number"
                    wire:model="expected_conversions"
                    class="w-full px-4 py-2 border rounded-lg @error('expected_conversions') border-red-500 @enderror"
                    min="0"
                >
                @error('expected_conversions')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">Status</label>
            <select
                wire:model="status"
                class="w-full px-4 py-2 border rounded-lg @error('status') border-red-500 @enderror"
            >
                <option value="active">Ativa</option>
                <option value="paused">Pausada</option>
                <option value="archived">Arquivada</option>
            </select>
            @error('status')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
        </div>

        <div class="flex gap-3">
            <button type="submit" class="px-6 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700">
                {{ $campaign ? 'Atualizar' : 'Criar' }} Campanha
            </button>
            <a href="{{ route('admin.affiliate.campaigns.index') }}" class="px-6 py-2 border rounded-lg hover:bg-slate-800">
                Cancelar
            </a>
        </div>
    </form>
</div>
