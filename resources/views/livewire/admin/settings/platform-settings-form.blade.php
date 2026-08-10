<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-white">⚙️ Configurações da Plataforma</h1>
    </div>

    <!-- Tabs -->
    <div class="bg-slate-900 rounded-lg shadow-lg border border-slate-800">
        <div class="flex border-b border-slate-800">
            <button
                wire:click="$set('tab', 'api_keys')"
                @class([
                    'flex-1 px-4 py-3 text-center font-medium transition',
                    'border-b-2 border-amber-400 text-amber-400' => $tab === 'api_keys',
                    'text-slate-400 hover:text-white' => $tab !== 'api_keys',
                ])
            >
                🔑 API Keys
            </button>
            <button
                wire:click="$set('tab', 'trends')"
                @class([
                    'flex-1 px-4 py-3 text-center font-medium transition',
                    'border-b-2 border-amber-400 text-amber-400' => $tab === 'trends',
                    'text-slate-400 hover:text-white' => $tab !== 'trends',
                ])
            >
                📊 Trends
            </button>
            <button
                wire:click="$set('tab', 'affiliate')"
                @class([
                    'flex-1 px-4 py-3 text-center font-medium transition',
                    'border-b-2 border-amber-400 text-amber-400' => $tab === 'affiliate',
                    'text-slate-400 hover:text-white' => $tab !== 'affiliate',
                ])
            >
                🤝 Afiliados
            </button>
        </div>

        <!-- API Keys Tab -->
        @if ($tab === 'api_keys')
            <div class="p-6 space-y-6">
                <div class="bg-slate-800/50 border border-slate-700 rounded-lg p-4">
                    <p class="text-sm text-slate-300">
                        <strong>💡 SerpAPI:</strong> Para integração com Google Trends, obtenha uma chave gratuita em
                        <a href="https://serpapi.com" target="_blank" class="text-amber-400 underline hover:text-amber-300">https://serpapi.com</a>
                        (100 requests/mês grátis)
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300">SerpAPI Key</label>
                    <input
                        type="password"
                        wire:model="serpapi_key"
                        placeholder="Deixe vazio para usar dados mock"
                        class="mt-1 block w-full rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:border-amber-400 focus:ring-amber-400"
                    />
                    <p class="mt-2 text-xs text-slate-400">
                        A chave será criptografada e armazenada no banco de dados. Apenas admins conseguem ver.
                    </p>
                </div>

                <button
                    wire:click="saveApiKeys"
                    class="w-full px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition font-medium"
                >
                    💾 Salvar API Keys
                </button>
            </div>
        @endif

        <!-- Trends Tab -->
        @if ($tab === 'trends')
            <div class="p-6 space-y-6">
                <div>
                    <label class="block text-sm font-medium text-slate-300">Região Padrão (Google Trends)</label>
                    <select wire:model="google_trends_region" class="mt-1 block w-full rounded-lg bg-slate-800 border border-slate-700 text-white focus:border-amber-400 focus:ring-amber-400">
                        @foreach ($regions as $code => $name)
                            <option value="{{ $code }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    <p class="mt-2 text-xs text-slate-400">
                        Qual região você quer monitorar por padrão? Você pode mudar isso ao buscar trending topics.
                    </p>
                </div>

                <button
                    wire:click="saveApiKeys"
                    class="w-full px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition font-medium"
                >
                    💾 Salvar Configurações de Trends
                </button>
            </div>
        @endif

        <!-- Affiliate Tab -->
        @if ($tab === 'affiliate')
            <div class="p-6 space-y-6">
                <div>
                    <label class="block text-sm font-medium text-slate-300">Comissão Padrão (%)</label>
                    <input
                        type="number"
                        wire:model="affiliate_commission_default"
                        min="0"
                        max="100"
                        step="0.5"
                        class="mt-1 block w-full rounded-lg bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:border-amber-400 focus:ring-amber-400"
                    />
                    <p class="mt-2 text-xs text-slate-400">
                        Comissão padrão para novos programas de afiliados (ex: 10 para 10%)
                    </p>
                </div>

                <button
                    wire:click="saveAffiliateSettings"
                    class="w-full px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition font-medium"
                >
                    💾 Salvar Configurações de Afiliados
                </button>
            </div>
        @endif
    </div>

    <!-- Info Card -->
    <div class="bg-slate-900/50 border border-slate-800 rounded-lg p-4">
        <p class="text-sm text-slate-300">
            ✅ <strong>Tudo configurável no painel!</strong> Nenhuma variável .env necessária. As configurações são salvas
            no banco de dados seguro e podem ser modificadas a qualquer momento sem redeploy.
        </p>
    </div>
</div>
