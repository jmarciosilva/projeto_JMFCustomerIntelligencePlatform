<div class="max-w-4xl">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-white">
            {{ $link ? 'Editar Link' : 'Novo Link' }}
        </h2>
    </div>

    @if (session()->has('message'))
        <div class="p-4 bg-emerald-500/20 text-emerald-200 rounded-lg mb-6 border border-emerald-500/30">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        <!-- Seção 1: Seleção de Produto -->
        <div class="bg-slate-900 border border-slate-800 rounded-lg p-6 space-y-4">
            <h3 class="text-lg font-semibold text-white">Produto de Afiliado</h3>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">Oportunidade de Produto (opcional)</label>
                <select
                    wire:model="product_opportunity_id"
                    class="w-full px-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-slate-300 focus:border-amber-600 focus:outline-none"
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
                <label class="block text-sm font-medium text-slate-300 mb-2">Produto de Afiliado *</label>
                <select
                    wire:model="affiliate_product_id"
                    @change="$wire.updatePreview()"
                    class="w-full px-4 py-2 bg-slate-800 border rounded-lg text-slate-300 focus:border-amber-600 focus:outline-none @error('affiliate_product_id') border-red-600 @else border-slate-700 @enderror"
                >
                    <option value="">Selecione um produto</option>
                    @foreach ($affiliateProducts as $product)
                        <option value="{{ $product->id }}">{{ $product->product_name }}</option>
                    @endforeach
                </select>
                @error('affiliate_product_id')<span class="text-red-400 text-sm">{{ $message }}</span>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">Campanha *</label>
                <select
                    wire:model="campaign_id"
                    class="w-full px-4 py-2 bg-slate-800 border rounded-lg text-slate-300 focus:border-amber-600 focus:outline-none @error('campaign_id') border-red-600 @else border-slate-700 @enderror"
                >
                    <option value="">Selecione uma campanha</option>
                    @foreach ($campaigns as $campaign)
                        <option value="{{ $campaign->id }}">{{ $campaign->name }}</option>
                    @endforeach
                </select>
                @error('campaign_id')<span class="text-red-400 text-sm">{{ $message }}</span>@enderror
            </div>
        </div>

        <!-- Seção 2: Slug -->
        <div class="bg-slate-900 border border-slate-800 rounded-lg p-6 space-y-4">
            <h3 class="text-lg font-semibold text-white">Slug do Link</h3>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">Slug *</label>
                <div class="flex gap-2">
                    <input
                        type="text"
                        wire:model="slug"
                        class="flex-1 px-4 py-2 bg-slate-800 border rounded-lg text-slate-300 placeholder-slate-500 focus:border-amber-600 focus:outline-none @error('slug') border-red-600 @else border-slate-700 @enderror"
                        placeholder="ex: iphone-15-review"
                        pattern="^[a-z0-9-]+$"
                    >
                    <button
                        type="button"
                        @click="$wire.generateSlug()"
                        class="px-4 py-2 bg-slate-700 text-slate-200 rounded-lg hover:bg-slate-600 transition-colors"
                    >
                        Gerar
                    </button>
                </div>
                @error('slug')<span class="text-red-400 text-sm">{{ $message }}</span>@enderror
                <p class="text-sm text-slate-400 mt-1">Link público: <code class="bg-slate-800 px-2 py-1 rounded text-amber-300">{{ config('app.url') }}/go/{{ $slug }}</code></p>
            </div>
        </div>

        <!-- Seção 3: Parâmetros UTM -->
        <div class="bg-slate-900 border border-slate-800 rounded-lg p-6 space-y-4">
            <h3 class="text-lg font-semibold text-white">Parâmetros UTM</h3>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">UTM Source</label>
                    <input
                        type="text"
                        wire:model="utm_source"
                        class="w-full px-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-slate-300 placeholder-slate-500 focus:border-amber-600 focus:outline-none"
                        placeholder="instagram"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">UTM Medium</label>
                    <input
                        type="text"
                        wire:model="utm_medium"
                        class="w-full px-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-slate-300 placeholder-slate-500 focus:border-amber-600 focus:outline-none"
                        placeholder="story"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">UTM Campaign</label>
                    <input
                        type="text"
                        wire:model="utm_campaign"
                        class="w-full px-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-slate-300 placeholder-slate-500 focus:border-amber-600 focus:outline-none"
                        placeholder="black-friday-2024"
                    >
                </div>
            </div>
        </div>

        <!-- Seção 4: Preview de URL -->
        @if ($redirectUrl)
            <div class="bg-slate-900 border border-amber-600/30 rounded-lg p-6 space-y-4">
                <h3 class="text-lg font-semibold text-white">Preview da URL de Redirecionamento</h3>
                <div class="bg-slate-800 p-4 rounded border border-slate-700 break-all font-mono text-sm text-slate-300">
                    {{ $redirectUrl }}
                </div>
                <p class="text-sm text-slate-400">
                    ℹ️ Esta é a URL final para a qual o usuário será redirecionado após clicar no link.
                </p>
            </div>
        @endif

        <!-- Seção 5: Status -->
        <div class="bg-slate-900 border border-slate-800 rounded-lg p-6 space-y-4">
            <h3 class="text-lg font-semibold text-white">Status</h3>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">Status *</label>
                <select
                    wire:model="status"
                    class="w-full px-4 py-2 bg-slate-800 border rounded-lg text-slate-300 focus:border-amber-600 focus:outline-none @error('status') border-red-600 @else border-slate-700 @enderror"
                >
                    <option value="active">Ativo</option>
                    <option value="paused">Pausado</option>
                    <option value="archived">Arquivado</option>
                </select>
                @error('status')<span class="text-red-400 text-sm">{{ $message }}</span>@enderror
            </div>
        </div>

        <!-- Botões de Ação -->
        <div class="flex gap-3">
            <button type="submit" class="px-6 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors font-medium">
                {{ $link ? 'Atualizar' : 'Criar' }} Link
            </button>
            <a href="{{ route('admin.affiliate.links.index') }}" class="px-6 py-2 border border-slate-700 text-slate-300 rounded-lg hover:bg-slate-800 transition-colors font-medium">
                Cancelar
            </a>
        </div>
    </form>
</div>
