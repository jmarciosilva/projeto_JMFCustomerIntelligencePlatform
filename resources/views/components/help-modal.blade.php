@props(['title' => 'Ajuda'])

<div x-data="{ open: false }" class="inline-flex">
    <button type="button" @click="open = true" aria-label="Ajuda"
            class="inline-flex h-6 w-6 items-center justify-center rounded-full border border-slate-700 text-xs text-slate-400 hover:border-amber-400 hover:text-amber-400">
        ?
    </button>

    <div x-show="open" x-cloak @keydown.escape.window="open = false" style="display: none"
         class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60" @click="open = false"></div>
        <div class="relative w-full max-w-lg rounded-xl border border-slate-800 bg-slate-900 p-6 shadow-xl">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-white">{{ $title }}</h3>
                <button type="button" @click="open = false" class="text-slate-500 hover:text-white">&times;</button>
            </div>
            <div class="space-y-3 text-sm text-slate-300">{{ $slot }}</div>
        </div>
    </div>
</div>
