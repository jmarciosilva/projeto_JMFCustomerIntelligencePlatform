<div class="fixed inset-0 z-50 overflow-y-auto" wire:show="true" style="display: none;">
    <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeModal()"></div>

        <div class="relative inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl sm:align-middle">
            <div class="border-b border-gray-200 bg-white px-6 py-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">
                        {{ $opportunity->trend->term }} — {{ $opportunity->affiliateProduct->name }}
                    </h3>
                    <button
                        wire:click="closeModal()"
                        class="text-gray-400 hover:text-gray-500"
                    >
                        <span class="sr-only">Fechar</span>
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="space-y-6 bg-white px-6 py-4">
                @if($errors->any())
                    <div class="rounded-md bg-red-50 p-4">
                        <p class="text-sm font-medium text-red-800">Erro ao processar ação:</p>
                        <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-red-700">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status Atual</label>
                        <p class="mt-1 inline-flex items-center rounded-full px-3 py-1 text-sm font-medium"
                            style="background-color: {{ $opportunity->status_sprint_a->color() }}20; color: {{ $opportunity->status_sprint_a->color() }};"
                        >
                            {{ $opportunity->status_sprint_a->label() }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Score de Oportunidade</label>
                        <p class="mt-1 text-2xl font-bold text-gray-900">{{ $opportunity->discovery_opportunity_score }}%</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Intenção de Compra</label>
                        <p class="mt-1 inline-flex items-center rounded-full px-3 py-1 text-sm font-medium"
                            style="background-color: {{ $opportunity->purchase_intent_label->color() }}20; color: {{ $opportunity->purchase_intent_label->color() }};"
                        >
                            {{ $opportunity->purchase_intent_label->label() }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Score de Intenção</label>
                        <p class="mt-1 text-2xl font-bold text-gray-900">{{ $opportunity->purchase_intent_score }}%</p>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-4">
                    <h4 class="text-sm font-medium text-gray-900">Breakdown de Oportunidade</h4>
                    <div class="mt-2 grid gap-2 text-sm text-gray-600">
                        @foreach($opportunity->opportunity_score_breakdown as $key => $value)
                            <div class="flex justify-between">
                                <span class="capitalize">{{ str_replace('_', ' ', $key) }}:</span>
                                <span class="font-medium">{{ $value }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                @if($showModal && $action)
                    <div class="border-t border-gray-200 pt-4">
                        <label class="block text-sm font-medium text-gray-700">
                            @if($action === 'approve')
                                Motivo da Aprovação
                            @elseif($action === 'reject')
                                Motivo da Rejeição
                            @endif
                        </label>
                        <textarea
                            wire:model="reason"
                            rows="3"
                            class="mt-2 w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:outline-none"
                            placeholder="Digite o motivo..."
                        ></textarea>
                        @error('reason') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                @endif
            </div>

            <div class="space-y-3 border-t border-gray-200 bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse sm:gap-3">
                @if($opportunity->status_sprint_a === \App\Domain\Affiliate\Enums\StatusSprintA::DISCOVERED)
                    @if(!$showModal)
                        <button
                            wire:click="openApproveModal()"
                            class="w-full rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 sm:w-auto"
                        >
                            Aprovar
                        </button>
                        <button
                            wire:click="openRejectModal()"
                            class="w-full rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 sm:w-auto"
                        >
                            Rejeitar
                        </button>
                    @else
                        @if($action === 'approve')
                            <button
                                wire:click="approve()"
                                class="w-full rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 sm:w-auto"
                            >
                                Confirmar Aprovação
                            </button>
                        @elseif($action === 'reject')
                            <button
                                wire:click="reject()"
                                class="w-full rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 sm:w-auto"
                            >
                                Confirmar Rejeição
                            </button>
                        @endif
                        <button
                            wire:click="closeModal()"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 sm:w-auto"
                        >
                            Cancelar
                        </button>
                    @endif
                @elseif($opportunity->status_sprint_a === \App\Domain\Affiliate\Enums\StatusSprintA::APPROVED)
                    <button
                        wire:click="publish()"
                        class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 sm:w-auto"
                    >
                        Publicar
                    </button>
                @endif

                <button
                    wire:click="closeModal()"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 sm:w-auto"
                >
                    Fechar
                </button>
            </div>
        </div>
    </div>
</div>
