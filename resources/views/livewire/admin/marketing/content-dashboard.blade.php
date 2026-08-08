<div>
    <x-slot:header>AI Marketing</x-slot:header>

    <x-slot:help>
        <x-help-modal title="Ajuda — AI Marketing">
            <p>Painel da <strong>Fase 15 (AI Marketing)</strong>: gera automaticamente título, descrição, palavras-chave de SEO, textos + hashtags para Instagram/Facebook/WhatsApp e uma campanha de e-mail marketing para um produto.</p>
            <p>Todo conteúdo nasce como <strong>rascunho</strong> — revise, edite se necessário e aprove antes de publicar. O driver ativo (indicado no topo) define se o texto vem de templates (sem custo) ou de uma IA real (Anthropic Claude), configurável em <code>.env</code>.</p>
        </x-help-modal>
    </x-slot:help>

    <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label for="applicationId" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Aplicação</label>
                <select wire:model.live="applicationId" id="applicationId"
                        class="rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                    @foreach ($applications as $app)
                        <option value="{{ $app->id }}">{{ $app->name }}</option>
                    @endforeach
                </select>
            </div>

            @if ($subjectIds->isNotEmpty())
                <div>
                    <label for="subjectId" class="block text-xs font-medium uppercase tracking-wide text-slate-500 mb-1">Produto</label>
                    <select wire:model.live="subjectId" id="subjectId"
                            class="rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                        @foreach ($subjectIds as $id)
                            <option value="{{ $id }}">Produto #{{ $id }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        <div class="flex items-center gap-3">
            <span class="text-[11px] font-medium uppercase px-2 py-1 rounded-full bg-slate-800 text-slate-400">
                Driver: {{ $activeDriver === 'anthropic' ? '🤖 Anthropic' : '📝 Template' }}
            </span>
            <button wire:click="$toggle('showForm')"
                    class="rounded-lg bg-amber-400 px-4 py-2 text-sm font-medium text-slate-950 hover:bg-amber-300 transition">
                ✨ Gerar conteúdo
            </button>
        </div>
    </div>

    @if ($showForm)
        <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5 mb-8">
            <h3 class="text-xs font-medium uppercase tracking-wide text-slate-500 mb-4">Novo produto</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-slate-500 mb-1">Nome do produto</label>
                    <input type="text" wire:model="productName"
                           class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                    @error('productName') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs text-slate-500 mb-1">Categoria</label>
                    <input type="text" wire:model="productCategory"
                           class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                    @error('productCategory') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs text-slate-500 mb-1">Preço (R$)</label>
                    <input type="number" step="0.01" wire:model="productPrice"
                           class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400">
                    @error('productPrice') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs text-slate-500 mb-1">Descrição (opcional, contexto extra)</label>
                    <textarea wire:model="productDescription" rows="2"
                              class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400"></textarea>
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <button wire:click="$set('showForm', false)" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-400 hover:text-white transition">
                    Cancelar
                </button>
                <button wire:click="generate" wire:loading.attr="disabled" wire:target="generate"
                        class="rounded-lg bg-amber-400 px-4 py-2 text-sm font-medium text-slate-950 hover:bg-amber-300 transition disabled:opacity-50">
                    <span wire:loading.remove wire:target="generate">Gerar pacote completo</span>
                    <span wire:loading wire:target="generate">Gerando…</span>
                </button>
            </div>
        </div>
    @endif

    @if (!$application)
        <p class="text-sm text-slate-500">Nenhuma aplicação cadastrada ainda.</p>
    @elseif ($subjectIds->isEmpty())
        <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-8 text-center">
            <p class="text-sm text-slate-500">Nenhum conteúdo gerado ainda para esta aplicação.</p>
            <p class="text-xs text-slate-600 mt-1">Clique em "Gerar conteúdo" para criar o primeiro pacote de marketing.</p>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @foreach ($content as $item)
                @php
                    $typeMeta = match ($item->type) {
                        'title' => ['icon' => '🏷️', 'label' => 'Título'],
                        'description' => ['icon' => '📝', 'label' => 'Descrição'],
                        'seo_keywords' => ['icon' => '🔍', 'label' => 'SEO / Palavras-chave'],
                        'social_instagram' => ['icon' => '📷', 'label' => 'Instagram'],
                        'social_facebook' => ['icon' => '👍', 'label' => 'Facebook'],
                        'social_whatsapp' => ['icon' => '💬', 'label' => 'WhatsApp'],
                        'email_campaign' => ['icon' => '✉️', 'label' => 'E-mail Marketing'],
                        default => ['icon' => '📄', 'label' => $item->type],
                    };
                    $statusClasses = match ($item->status) {
                        'approved' => 'bg-emerald-400/10 text-emerald-400',
                        'rejected' => 'bg-red-400/10 text-red-400',
                        default => 'bg-slate-700/50 text-slate-400',
                    };
                    $statusLabel = match ($item->status) {
                        'approved' => 'Aprovado',
                        'rejected' => 'Rejeitado',
                        default => 'Rascunho',
                    };
                @endphp
                <div class="rounded-xl border border-slate-800 bg-slate-900/60 p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">{{ $typeMeta['icon'] }}</span>
                            <h4 class="text-sm font-semibold text-white">{{ $typeMeta['label'] }}</h4>
                        </div>
                        <span class="text-[11px] font-medium uppercase px-2 py-0.5 rounded-full {{ $statusClasses }}">
                            {{ $statusLabel }}
                        </span>
                    </div>

                    @if ($item->type === 'email_campaign' && isset($item->metadata['subject']))
                        <p class="text-xs text-slate-500 mb-1">Assunto: <span class="text-slate-300">{{ $item->metadata['subject'] }}</span></p>
                    @endif

                    @if ($editingId === $item->id)
                        <textarea wire:model="editingContent" rows="4"
                                  class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-100 focus:border-amber-400 focus:outline-none focus:ring-1 focus:ring-amber-400"></textarea>
                        <div class="flex justify-end gap-2 mt-3">
                            <button wire:click="cancelEdit" class="rounded-lg px-3 py-1.5 text-xs font-medium text-slate-400 hover:text-white transition">
                                Cancelar
                            </button>
                            <button wire:click="saveEdit" class="rounded-lg bg-amber-400 px-3 py-1.5 text-xs font-medium text-slate-950 hover:bg-amber-300 transition">
                                Salvar e aprovar
                            </button>
                        </div>
                    @else
                        <p class="text-sm text-slate-300 whitespace-pre-line">{{ $item->content }}</p>

                        @if ($item->type === 'seo_keywords' && isset($item->metadata['keywords']))
                            <div class="flex flex-wrap gap-1.5 mt-3">
                                @foreach ($item->metadata['keywords'] as $keyword)
                                    <span class="text-[11px] px-2 py-0.5 rounded-full bg-slate-800 text-slate-400">{{ $keyword }}</span>
                                @endforeach
                            </div>
                        @endif

                        @if (str_starts_with($item->type, 'social_') && isset($item->metadata['hashtags']))
                            <div class="flex flex-wrap gap-1.5 mt-3">
                                @foreach ($item->metadata['hashtags'] as $hashtag)
                                    <span class="text-[11px] px-2 py-0.5 rounded-full bg-blue-400/10 text-blue-400">{{ $hashtag }}</span>
                                @endforeach
                            </div>
                        @endif

                        <div class="flex justify-end gap-2 mt-4">
                            <button wire:click="startEdit({{ $item->id }})" class="rounded-lg px-3 py-1.5 text-xs font-medium text-slate-400 hover:text-white transition">
                                ✏️ Editar
                            </button>
                            @if ($item->status !== 'rejected')
                                <button wire:click="reject({{ $item->id }})" class="rounded-lg px-3 py-1.5 text-xs font-medium text-red-400 hover:text-red-300 transition">
                                    Rejeitar
                                </button>
                            @endif
                            @if ($item->status !== 'approved')
                                <button wire:click="approve({{ $item->id }})" class="rounded-lg bg-emerald-400/10 px-3 py-1.5 text-xs font-medium text-emerald-400 hover:bg-emerald-400/20 transition">
                                    ✓ Aprovar
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
