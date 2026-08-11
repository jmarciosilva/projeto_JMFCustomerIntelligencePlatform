<div>
    <x-slot:header>Oportunidades de Produtos</x-slot:header>

    <x-slot:help>
        <x-help-modal title="Ajuda — Oportunidades de Produtos">
            <p>Gerencie, aprove e publique oportunidades de produtos de afiliados.</p>
            <p><strong>Filtros:</strong> Use o campo de busca para procurar por tendência ou produto, e o dropdown de status para filtrar por estado (DISCOVERED, ANALYZING, APPROVED, etc).</p>
            <p><strong>Ações:</strong> Clique em "Curar" para aprovar, rejeitar ou publicar uma oportunidade. Uma oportunidade deve estar em status APPROVED antes de poder ser publicada.</p>
        </x-help-modal>
    </x-slot:help>

    <livewire:admin.affiliate.product-opportunities-list />
</div>
