<div class="max-w-2xl">
    <div class="mb-6">
        <h2 class="text-2xl font-bold">
            {{ $content ? 'Editar Conteúdo' : 'Novo Conteúdo' }}
        </h2>
    </div>

    @if (session()->has('message'))
        <div class="p-4 bg-green-100 text-green-800 rounded-lg mb-6">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit="save" class="space-y-6 bg-white rounded-lg shadow p-6">
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

        <div>
            <label class="block text-sm font-medium mb-2">Oportunidade de Produto (opcional)</label>
            <select
                wire:model="product_opportunity_id"
                class="w-full px-4 py-2 border rounded-lg"
            >
                <option value="">Nenhuma oportunidade</option>
                @foreach ($opportunities as $opportunity)
                    <option value="{{ $opportunity->id }}">
                        {{ $opportunity->product_name }} (Score: {{ $opportunity->opportunity_score }})
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">Título *</label>
            <input
                type="text"
                wire:model="title"
                class="w-full px-4 py-2 border rounded-lg @error('title') border-red-500 @enderror"
                placeholder="Ex: Análise do iPhone 15"
            >
            @error('title')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">Descrição</label>
            <textarea
                wire:model="description"
                class="w-full px-4 py-2 border rounded-lg @error('description') border-red-500 @enderror"
                rows="4"
                placeholder="Descreva o conteúdo"
            ></textarea>
            @error('description')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2">Tipo de Conteúdo *</label>
                <select
                    wire:model="content_type"
                    class="w-full px-4 py-2 border rounded-lg @error('content_type') border-red-500 @enderror"
                >
                    <option value="blog_post">Blog Post</option>
                    <option value="social_media">Social Media</option>
                    <option value="email">Email</option>
                    <option value="video">Vídeo</option>
                    <option value="other">Outro</option>
                </select>
                @error('content_type')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Plataforma *</label>
                <select
                    wire:model="platform"
                    class="w-full px-4 py-2 border rounded-lg @error('platform') border-red-500 @enderror"
                >
                    <option value="website">Website</option>
                    <option value="instagram">Instagram</option>
                    <option value="facebook">Facebook</option>
                    <option value="whatsapp">WhatsApp</option>
                    <option value="email">Email</option>
                    <option value="other">Outro</option>
                </select>
                @error('platform')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">URL (opcional)</label>
            <input
                type="url"
                wire:model="url"
                class="w-full px-4 py-2 border rounded-lg @error('url') border-red-500 @enderror"
                placeholder="https://exemplo.com"
            >
            @error('url')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2">Status *</label>
                <select
                    wire:model="status"
                    class="w-full px-4 py-2 border rounded-lg @error('status') border-red-500 @enderror"
                >
                    <option value="draft">Rascunho</option>
                    <option value="published">Publicado</option>
                    <option value="archived">Arquivado</option>
                </select>
                @error('status')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Data de Publicação</label>
                <input
                    type="datetime-local"
                    wire:model="published_at"
                    class="w-full px-4 py-2 border rounded-lg @error('published_at') border-red-500 @enderror"
                >
                @error('published_at')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                {{ $content ? 'Atualizar' : 'Criar' }} Conteúdo
            </button>
            <a href="{{ route('admin.affiliate.content.index') }}" class="px-6 py-2 border rounded-lg hover:bg-gray-50">
                Cancelar
            </a>
        </div>
    </form>
</div>
