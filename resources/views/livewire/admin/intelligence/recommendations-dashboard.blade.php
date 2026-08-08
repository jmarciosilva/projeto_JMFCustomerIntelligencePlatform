<div>
    <x-slot:header>Recomendações IA</x-slot:header>

    <x-slot:help>
        <x-help-modal title="Ajuda — Recomendações IA">
            <p>Painel da <strong>Fase 14 (AI Business Assistant)</strong>: recomendações textuais e acionáveis geradas automaticamente para cada vendedor, a partir dos dados de Analytics, CRM e Business Intelligence já coletados.</p>
            <p>Tipos: queda de vendas, oportunidade de kit, preço fora da média da categoria e horário ideal de venda. Atualizadas diariamente pelo comando <code>intelligence:generate-recommendations</code>, ou gere agora pelo botão abaixo.</p>
        </x-help-modal>
    </x-slot:help>

    <div class="flex flex-wrap items-end gap-4 mb-8">
        <div>
            <label for="applicationId" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Aplicação</label>
            <select wire:model.live="applicationId" id="applicationId"
                    class="rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                @foreach ($applications as $app)
                    <option value="{{ $app->id }}">{{ $app->name }}</option>
                @endforeach
            </select>
        </div>

        @if ($sellerIds->isNotEmpty())
            <div>
                <label for="sellerId" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Vendedor</label>
                <select wire:model.live="sellerId" id="sellerId"
                        class="rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                    @foreach ($sellerIds as $id)
                        <option value="{{ $id }}">Vendedor #{{ $id }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <button wire:click="generate" wire:loading.attr="disabled" wire:target="generate"
                class="rounded-lg bg-amber-400 px-4 py-2 text-sm font-medium text-slate-950 hover:bg-amber-300 transition disabled:opacity-50">
            <span wire:loading.remove wire:target="generate">🔄 Gerar agora</span>
            <span wire:loading wire:target="generate">Gerando…</span>
        </button>
    </div>

    @if (!$application)
        <p class="text-sm text-slate-500">Nenhuma aplicação cadastrada ainda.</p>
    @elseif ($sellerIds->isEmpty())
        <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-8 text-center">
            <p class="text-sm text-slate-500">Nenhuma recomendação gerada ainda para esta aplicação.</p>
            <p class="text-xs text-slate-600 mt-1">Clique em "Gerar agora" para calcular a partir dos dados existentes.</p>
        </div>
    @else
        <div class="space-y-4">
            @forelse ($recommendations as $recommendation)
                @php
                    $typeMeta = match ($recommendation->type) {
                        'sales_drop' => ['icon' => '📉', 'label' => 'Queda de vendas', 'classes' => 'bg-red-400/10 text-red-400'],
                        'kit_opportunity' => ['icon' => '🎁', 'label' => 'Oportunidade de kit', 'classes' => 'bg-purple-400/10 text-purple-400'],
                        'price_outlier' => ['icon' => '💲', 'label' => 'Preço fora da média', 'classes' => 'bg-amber-400/10 text-amber-400'],
                        'ideal_timing' => ['icon' => '⏰', 'label' => 'Horário ideal', 'classes' => 'bg-blue-400/10 text-blue-400'],
                        default => ['icon' => '💡', 'label' => $recommendation->type, 'classes' => 'bg-slate-700/50 text-slate-400'],
                    };
                    $priorityWidth = min(100, (float) $recommendation->priority);
                @endphp
                <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <span class="text-2xl">{{ $typeMeta['icon'] }}</span>
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-[11px] font-medium uppercase px-2 py-0.5 rounded-full {{ $typeMeta['classes'] }}">
                                        {{ $typeMeta['label'] }}
                                    </span>
                                </div>
                                <h4 class="text-sm font-semibold text-white">{{ $recommendation->title }}</h4>
                                <p class="text-sm text-slate-400 mt-1">{{ $recommendation->message }}</p>
                            </div>
                        </div>

                        <div class="text-right shrink-0">
                            <span class="text-xs text-slate-500 uppercase tracking-wide">Impacto</span>
                            <div class="text-lg font-semibold text-white">{{ number_format($recommendation->priority, 0) }}</div>
                        </div>
                    </div>

                    <div class="h-1.5 rounded-full bg-slate-800 overflow-hidden mt-4">
                        <div class="h-full rounded-full bg-amber-400" style="width: {{ $priorityWidth }}%"></div>
                    </div>
                </div>
            @empty
                <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-8 text-center">
                    <p class="text-sm text-slate-500">Nenhuma recomendação para o vendedor #{{ $sellerId }} no momento.</p>
                </div>
            @endforelse
        </div>
    @endif
</div>
