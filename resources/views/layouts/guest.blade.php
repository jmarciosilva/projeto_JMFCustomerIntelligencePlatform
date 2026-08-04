<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} — Login</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-950 text-slate-100 antialiased">
    <div class="min-h-full flex flex-col items-center justify-center px-6 py-16">
        <div class="w-full max-w-sm">
            <div class="flex items-center gap-3 mb-8 justify-center">
                <span class="inline-flex h-2.5 w-2.5 rounded-full bg-amber-400"></span>
                <span class="text-sm font-medium uppercase tracking-wider text-amber-400">{{ config('app.name') }}</span>
            </div>

            {{ $slot }}

            <p class="mt-10 text-center text-xs text-slate-600">
                JMF System &middot; Desenvolvimento privado
            </p>
        </div>
    </div>
</body>
</html>
