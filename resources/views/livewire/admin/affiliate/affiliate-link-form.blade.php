<div class="max-w-4xl">
    <div class="mb-6">
        <h2 class="text-2xl font-bold">
            {{ $link ? 'Editar Link' : 'Novo Link' }}
        </h2>
    </div>

    @if (session()->has('message'))
        <div class="p-4 bg-green-100 text-green-800 rounded-lg mb-6">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        <!-- Seção 1: Seleção de Produto -->
        <div class="bg-white rounded-lg shadow p-6 space-y-4">
            <h3 class="text-lg font-semibold">Produto de Afiliado</h3>

            <div>
                <label class="block text-sm font-medium mb-2">Oportunidade de Produto (opcional)</label>
                <select
                    wire:model="product_opportunity_id"
                    class="w-full px-4 py-2 border rounded-lg"
                >
                    <option value="0">Nenhuma oportunidade</option>
                    @foreach ($opportunities as $opportunity)
                        <option value="{{ $opportunity->id }}">
                            {{ $opportunity->product_name }} (Score: {{ $opportunity->opportunity_score }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Produto de Afiliado *</label>
                <select
                    wire:model="affiliate_product_id"
                    @change="$wire.updatePreview()"
                    class="w-full px-4 py-2 border rounded-lg @error('affiliate_product_id') border-red-500 @enderror"
                >
                    <option value="">Selecione um produto</option>
                    @foreach ($affiliateProducts as $product)
                        <option value="{{ $product->id }}">{{ $product->product_name }}</option>
                    @endforeach
                </select>
                @error('affiliate_product_id')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Campanha *</label>
                <select
                    wire:model="campaign_id"
                    class="w-full px-4 py-2 border rounded-lg @error('campaign_id') border-red-500 @enderror"
                >
                    <option value="">Selecione uma campanha</option>
                    @foreach ($campaigns as $campaign)
                        <option value="{{ $campaign->id }}">{{ $campaign->name }}</option>
                    @endforeach
                </select>
                @error('campaign_id')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
            </div>
        </div>

        <!-- Seção 2: Slug -->
        <div class="bg-white rounded-lg shadow p-6 space-y-4">
            <h3 class="text-lg font-semibold">Slug do Link</h3>

            <div>
                <label class="block text-sm font-medium mb-2">Slug *</label>
                <div class="flex gap-2">
                    <input
                        type="text"
                        wire:model="slug"
                        class="flex-1 px-4 py-2 border rounded-lg @error('slug') border-red-500 @enderror"
                        placeholder="ex: iphone-15-review"
                        pattern="^[a-z0-9-]+$"
                    >
                    <button
                        type="button"
                        @click="$wire.generateSlug()"
                        class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700"
                    >
                        Gerar
                    </button>
                </div>
                @error('slug')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                <p class="text-sm text-gray-500 mt-1">Link público: <code>{{ config('app.url') }}/go/{{ $slug }}</code></p>
            </div>
        </div>

        <!-- Seção 3: Parâmetros UTM -->
        <div class="bg-white rounded-lg shadow p-6 space-y-4">
            <h3 class="text-lg font-semibold">Parâmetros UTM</h3>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">UTM Source</label>
                    <input
                        type="text"
                        wire:model="utm_source"
                        class="w-full px-4 py-2 border rounded-lg"
                        placeholder="instagram"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">UTM Medium</label>
                    <input
                        type="text"
                        wire:model="utm_medium"
                        class="w-full px-4 py-2 border rounded-lg"
                        placeholder="story"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">UTM Campaign</label>
                    <input
                        type="text"
                        wire:model="utm_campaign"
                        class="w-full px-4 py-2 border rounded-lg"
                        placeholder="black-friday-2024"
                    >
                </div>
            </div>
        </div>

        <!-- Seção 4: Preview de URL -->
        @if ($redirectUrl)
            <div class="bg-blue-50 rounded-lg shadow p-6 space-y-4 border border-blue-200">
                <h3 class="text-lg font-semibold text-blue-900">Preview da URL de Redirecionamento</h3>
                <div class="bg-white p-4 rounded border border-blue-300 break-all font-mono text-sm">
                    {{ $redirectUrl }}
                </div>
                <p class="text-sm text-blue-700">
                    ℹ️ Esta é a URL final para a qual o usuário será redirecionado após clicar no link.
                </p>
            </div>
        @endif

        <!-- Seção 5: Status -->
        <div class="bg-white rounded-lg shadow p-6 space-y-4">
            <h3 class="text-lg font-semibold">Status</h3>

            <div>
                <label class="block text-sm font-medium mb-2">Status *</label>
                <select
                    wire:model="status"
                    class="w-full px-4 py-2 border rounded-lg @error('status') border-red-500 @enderror"
                >
                    <option value="active">Ativo</option>
                    <option value="paused">Pausado</option>
                    <option value="archived">Arquivado</option>
                </select>
                @error('status')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
            </div>
        </div>

        <!-- Botões de Ação -->
        <div class="flex gap-3">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                {{ $link ? 'Atualizar' : 'Criar' }} Link
            </button>
            <a href="{{ route('admin.affiliate.links.index') }}" class="px-6 py-2 border rounded-lg hover:bg-gray-50">
                Cancelar
            </a>
        </div>
    </form>
</div>
