<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full bg-slate-950 text-slate-100 antialiased">
    <div class="min-h-full flex">
        <aside class="hidden md:flex md:w-64 md:flex-col border-r border-slate-800 bg-slate-900/50">
            <div class="flex items-center gap-3 px-6 py-6">
                <span class="inline-flex h-2.5 w-2.5 rounded-full bg-amber-400"></span>
                <span class="text-sm font-medium uppercase tracking-wider text-amber-400">{{ config('app.name') }}</span>
            </div>

            <nav class="flex-1 px-3 py-4 space-y-1">
                <a href="{{ route('admin.dashboard') }}"
                   class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    Dashboard
                </a>

                @can('users.view')
                    <a href="{{ route('admin.users.index') }}"
                       class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.users.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        Usuários
                    </a>
                @endcan

                @can('tenants.view')
                    <a href="{{ route('admin.tenants.index') }}"
                       class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.tenants.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        Tenants
                    </a>
                @endcan

                @can('applications.view')
                    <a href="{{ route('admin.applications.index') }}"
                       class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.applications.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        Aplicações
                    </a>
                @endcan

                @can('audit.view')
                    <a href="{{ route('admin.audit.index') }}"
                       class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.audit.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        Auditoria
                    </a>
                @endcan
            </nav>

            <div class="px-3 py-4 border-t border-slate-800">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left rounded-lg px-3 py-2 text-sm font-medium text-slate-400 hover:bg-slate-800 hover:text-white">
                        Sair
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex-1 flex flex-col min-w-0">
            <header class="border-b border-slate-800 px-6 py-4 flex items-center justify-between">
                <h1 class="text-lg font-semibold text-white">{{ $header ?? '' }}</h1>
                <a href="{{ route('admin.profile') }}" class="text-sm text-slate-400 hover:text-amber-400">
                    {{ auth()->user()?->name }}
                </a>
            </header>

            <main class="flex-1 px-6 py-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>
