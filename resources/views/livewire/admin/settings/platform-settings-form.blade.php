<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">⚙️ Configurações da Plataforma</h1>
    </div>

    <!-- Tabs -->
    <div class="bg-white rounded-lg shadow">
        <div class="flex border-b">
            <button
                wire:click="$set('tab', 'api_keys')"
                @class([
                    'flex-1 px-4 py-3 text-center font-medium',
                    'border-b-2 border-blue-600 text-blue-600' => $tab === 'api_keys',
                    'text-gray-600 hover:text-gray-900' => $tab !== 'api_keys',
                ])
            >
                🔑 API Keys
            </button>
            <button
                wire:click="$set('tab', 'trends')"
                @class([
                    'flex-1 px-4 py-3 text-center font-medium',
                    'border-b-2 border-blue-600 text-blue-600' => $tab === 'trends',
                    'text-gray-600 hover:text-gray-900' => $tab !== 'trends',
                ])
            >
                📊 Trends
            </button>
            <button
                wire:click="$set('tab', 'affiliate')"
                @class([
                    'flex-1 px-4 py-3 text-center font-medium',
                    'border-b-2 border-blue-600 text-blue-600' => $tab === 'affiliate',
                    'text-gray-600 hover:text-gray-900' => $tab !== 'affiliate',
                ])
            >
                🤝 Afiliados
            </button>
        </div>

        <!-- API Keys Tab -->
        @if ($tab === 'api_keys')
            <div class="p-6 space-y-6">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm text-blue-800">
                        <strong>💡 SerpAPI:</strong> Para integração com Google Trends, obtenha uma chave gratuita em
                        <a href="https://serpapi.com" target="_blank" class="underline">https://serpapi.com</a>
                        (100 requests/mês grátis)
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">SerpAPI Key</label>
                    <input
                        type="password"
                        wire:model="serpapi_key"
                        placeholder="Deixe vazio para usar dados mock"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm"
                    />
                    <p class="mt-2 text-xs text-gray-500">
                        A chave será criptografada e armazenada no banco de dados. Apenas admins conseguem ver.
                    </p>
                </div>

                <button
                    wire:click="saveApiKeys"
                    class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                >
                    💾 Salvar API Keys
                </button>
            </div>
        @endif

        <!-- Trends Tab -->
        @if ($tab === 'trends')
            <div class="p-6 space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Região Padrão (Google Trends)</label>
                    <select wire:model="google_trends_region" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">
                        @foreach ($regions as $code => $name)
                            <option value="{{ $code }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    <p class="mt-2 text-xs text-gray-500">
                        Qual região você quer monitorar por padrão? Você pode mudar isso ao buscar trending topics.
                    </p>
                </div>

                <button
                    wire:click="saveApiKeys"
                    class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                >
                    💾 Salvar Configurações de Trends
                </button>
            </div>
        @endif

        <!-- Affiliate Tab -->
        @if ($tab === 'affiliate')
            <div class="p-6 space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Comissão Padrão (%)</label>
                    <input
                        type="number"
                        wire:model="affiliate_commission_default"
                        min="0"
                        max="100"
                        step="0.5"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm"
                    />
                    <p class="mt-2 text-xs text-gray-500">
                        Comissão padrão para novos programas de afiliados (ex: 10 para 10%)
                    </p>
                </div>

                <button
                    wire:click="saveAffiliateSettings"
                    class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                >
                    💾 Salvar Configurações de Afiliados
                </button>
            </div>
        @endif
    </div>

    <!-- Info Card -->
    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
        <p class="text-sm text-green-800">
            ✅ <strong>Tudo configurável no painel!</strong> Nenhuma variável .env necessária. As configurações são salvas
            no banco de dados seguro e podem ser modificadas a qualquer momento sem redeploy.
        </p>
    </div>
</div>
