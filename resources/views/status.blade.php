<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $appName }} — Status técnico</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-950 text-slate-100 antialiased">
    <div class="min-h-full flex flex-col items-center justify-center px-6 py-16">
        <div class="w-full max-w-2xl">
            <div class="flex items-center gap-3 mb-8">
                <span class="inline-flex h-2.5 w-2.5 rounded-full bg-amber-400"></span>
                <span class="text-sm font-medium uppercase tracking-wider text-amber-400">Em desenvolvimento (MVP)</span>
            </div>

            <h1 class="text-3xl sm:text-4xl font-semibold tracking-tight text-white">
                {{ $appName }}
            </h1>
            <p class="mt-3 text-slate-400">
                Plataforma central de inteligência de clientes — Fase 01: Fundação e documentação.
            </p>

            <dl class="mt-10 grid grid-cols-1 sm:grid-cols-2 gap-px overflow-hidden rounded-xl border border-slate-800 bg-slate-800">
                <div class="bg-slate-900 p-5">
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Ambiente</dt>
                    <dd class="mt-1 text-sm text-slate-200">{{ $appEnv }}</dd>
                </div>
                <div class="bg-slate-900 p-5">
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">PHP</dt>
                    <dd class="mt-1 text-sm text-slate-200">{{ $phpVersion }}</dd>
                </div>
                <div class="bg-slate-900 p-5">
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Laravel</dt>
                    <dd class="mt-1 text-sm text-slate-200">{{ $laravelVersion }}</dd>
                </div>
                <div class="bg-slate-900 p-5">
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Banco de dados</dt>
                    <dd class="mt-1 text-sm flex items-center gap-2">
                        <span class="inline-flex h-2 w-2 rounded-full {{ $databaseStatus ? 'bg-emerald-400' : 'bg-rose-500' }}"></span>
                        <span class="text-slate-200">{{ $databaseStatus ? 'Conectado' : 'Indisponível' }}</span>
                    </dd>
                </div>
                <div class="bg-slate-900 p-5">
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Fila</dt>
                    <dd class="mt-1 text-sm text-slate-200">{{ $queueConnection }}</dd>
                </div>
                <div class="bg-slate-900 p-5">
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Cache</dt>
                    <dd class="mt-1 text-sm text-slate-200">{{ $cacheStore }}</dd>
                </div>
                <div class="bg-slate-900 p-5 sm:col-span-2">
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Sessão</dt>
                    <dd class="mt-1 text-sm text-slate-200">{{ $sessionDriver }}</dd>
                </div>
            </dl>

            <p class="mt-10 text-xs text-slate-600">
                JMF System &middot; Desenvolvimento privado
            </p>
        </div>
    </div>
</body>
</html>
