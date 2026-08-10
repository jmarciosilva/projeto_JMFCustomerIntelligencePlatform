<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold">Conteúdos Publicados</h2>
        <a href="{{ route('admin.affiliate.content.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Novo Conteúdo
        </a>
    </div>

    @if (session()->has('message'))
        <div class="p-4 bg-green-100 text-green-800 rounded-lg">
            {{ session('message') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b space-y-3">
            <input
                type="text"
                wire:model.live="search"
                placeholder="Buscar por título..."
                class="w-full px-4 py-2 border rounded-lg"
            >
            <select
                wire:model.live="campaign_id"
                class="w-full px-4 py-2 border rounded-lg"
            >
                <option value="">Todas as campanhas</option>
                @foreach ($campaigns as $campaign)
                    <option value="{{ $campaign->id }}">{{ $campaign->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Título</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Campanha</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Tipo</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Plataforma</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($contents as $content)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium">{{ $content->title }}</td>
                            <td class="px-6 py-4">{{ $content->campaign->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm">
                                @php
                                    $types = [
                                        'blog_post' => 'Blog Post',
                                        'social_media' => 'Social Media',
                                        'email' => 'Email',
                                        'video' => 'Vídeo',
                                        'other' => 'Outro',
                                    ];
                                @endphp
                                {{ $types[$content->content_type] ?? $content->content_type }}
                            </td>
                            <td class="px-6 py-4 text-sm capitalize">{{ $content->platform }}</td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-sm font-medium
                                    {{ $content->status === 'published' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $content->status === 'draft' ? 'bg-gray-100 text-gray-800' : '' }}
                                    {{ $content->status === 'archived' ? 'bg-red-100 text-red-800' : '' }}
                                ">
                                    {{ ucfirst($content->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 flex gap-2">
                                <a href="{{ route('admin.affiliate.content.edit', $content) }}" class="text-blue-600 hover:text-blue-800">
                                    Editar
                                </a>
                                <button wire:click="delete({{ $content->id }})" onclick="return confirm('Tem certeza?')" class="text-red-600 hover:text-red-800">
                                    Deletar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                Nenhum conteúdo encontrado
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t">
            {{ $contents->links() }}
        </div>
    </div>
</div>
