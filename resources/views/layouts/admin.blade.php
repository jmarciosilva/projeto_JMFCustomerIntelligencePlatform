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

            <nav class="flex-1 px-3 py-4 space-y-6 overflow-y-auto">
                <!-- Guia do Laboratório -->
                <div class="mb-6">
                    <a href="{{ route('admin.affiliate.guide') }}"
                       class="block rounded-lg px-4 py-3 bg-gradient-to-r from-amber-600/30 to-amber-500/20 border border-amber-600/40 text-amber-300 font-semibold text-sm hover:from-amber-600/40 hover:to-amber-500/30 transition-all">
                        📚 Guia do Laboratório
                    </a>
                </div>

                <!-- Sistema Principal -->
                <div class="space-y-1">
                    <p class="px-3 py-2 text-xs font-semibold text-slate-500 uppercase tracking-wider">Sistema Principal</p>
                    <a href="{{ route('admin.dashboard') }}"
                       class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        📊 Dashboard
                    </a>

                    @can('users.view')
                        <a href="{{ route('admin.users.index') }}"
                           class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.users.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            👥 Usuários
                        </a>
                    @endcan

                    @can('tenants.view')
                        <a href="{{ route('admin.tenants.index') }}"
                           class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.tenants.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            🏢 Tenants
                        </a>
                    @endcan

                    @can('applications.view')
                        <a href="{{ route('admin.applications.index') }}"
                           class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.applications.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            📱 Aplicações
                        </a>
                    @endcan
                </div>

                <!-- Analytics & Inteligência -->
                <div class="space-y-1">
                    <p class="px-3 py-2 text-xs font-semibold text-slate-500 uppercase tracking-wider">Analytics & Inteligência</p>
                    @can('analytics.view')
                        <a href="{{ route('admin.analytics.index') }}"
                           class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.analytics.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            📈 Analytics
                        </a>
                    @endcan

                    <a href="{{ route('admin.marketplace.dashboard') }}"
                       class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.marketplace.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        🛍️ Marketplace
                    </a>

                    <a href="{{ route('admin.intelligence.dashboard') }}"
                       class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.intelligence.dashboard') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        🧠 Business Intelligence
                    </a>

                    <a href="{{ route('admin.intelligence.recommendations') }}"
                       class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.intelligence.recommendations') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        💡 Recomendações IA
                    </a>

                    <a href="{{ route('admin.marketing.dashboard') }}"
                       class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.marketing.dashboard') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        ✍️ AI Marketing
                    </a>

                    @can('contacts.view')
                        <a href="{{ route('admin.contacts.index') }}"
                           class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.contacts.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            👤 Contatos
                        </a>
                    @endcan
                </div>

                <!-- 🎯 Laboratório de Afiliados -->
                <div class="space-y-1 border-t border-slate-800 pt-4">
                    <p class="px-3 py-2 text-xs font-semibold text-amber-500 uppercase tracking-wider">🎯 Laboratório de Afiliados</p>

                    <!-- Setup -->
                    <div class="space-y-1">
                        <p class="px-3 py-1 text-xs text-slate-500">Setup</p>
                        @can('affiliate_programs.view')
                            <a href="{{ route('admin.affiliate.programs.index') }}"
                               class="block rounded-lg px-3 py-2 text-sm font-medium ml-2 {{ request()->routeIs('admin.affiliate.programs.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                🤝 Programas
                            </a>
                        @endcan

                        @can('affiliate_products.view')
                            <a href="{{ route('admin.affiliate.products.index') }}"
                               class="block rounded-lg px-3 py-2 text-sm font-medium ml-2 {{ request()->routeIs('admin.affiliate.products.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                📦 Produtos
                            </a>

                            <a href="{{ route('admin.affiliate.products.suggestions') }}"
                               class="block rounded-lg px-3 py-2 text-sm font-medium ml-2 {{ request()->routeIs('admin.affiliate.products.suggestions') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                ✨ Sugestões por Trends
                            </a>
                        @endcan
                    </div>

                    <!-- Campanhas -->
                    <div class="space-y-1">
                        <p class="px-3 py-1 text-xs text-slate-500">Campanhas & Conteúdo</p>
                        <a href="{{ route('admin.affiliate.campaigns.index') }}"
                           class="block rounded-lg px-3 py-2 text-sm font-medium ml-2 {{ request()->routeIs('admin.affiliate.campaigns.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            📋 Campanhas
                        </a>

                        <a href="{{ route('admin.affiliate.content.index') }}"
                           class="block rounded-lg px-3 py-2 text-sm font-medium ml-2 {{ request()->routeIs('admin.affiliate.content.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            ✨ Conteúdos
                        </a>

                        <a href="{{ route('admin.affiliate.links.index') }}"
                           class="block rounded-lg px-3 py-2 text-sm font-medium ml-2 {{ request()->routeIs('admin.affiliate.links.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            🔗 Links
                        </a>
                    </div>

                    <!-- Performance -->
                    <div class="space-y-1">
                        <p class="px-3 py-1 text-xs text-slate-500">Performance & Conversões</p>
                        <a href="{{ route('admin.affiliate.conversions.index') }}"
                           class="block rounded-lg px-3 py-2 text-sm font-medium ml-2 {{ request()->routeIs('admin.affiliate.conversions.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            💰 Conversões
                        </a>

                        <a href="{{ route('admin.affiliate.analytics.index') }}"
                           class="block rounded-lg px-3 py-2 text-sm font-medium ml-2 {{ request()->routeIs('admin.affiliate.analytics.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            📊 Analytics
                        </a>

                        <a href="{{ route('admin.affiliate.recommendations.index') }}"
                           class="block rounded-lg px-3 py-2 text-sm font-medium ml-2 {{ request()->routeIs('admin.affiliate.recommendations.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            🎯 Recomendações
                        </a>
                    </div>

                    <!-- Tendências -->
                    @can('watchlists.view')
                        <div class="space-y-1">
                            <p class="px-3 py-1 text-xs text-slate-500">Curadoria de Tendências</p>
                            <a href="{{ route('admin.trends.watchlists.index') }}"
                               class="block rounded-lg px-3 py-2 text-sm font-medium ml-2 {{ request()->routeIs('admin.trends.watchlists.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                📈 Watchlists
                            </a>

                            <a href="{{ route('admin.trends.google-trends') }}"
                               class="block rounded-lg px-3 py-2 text-sm font-medium ml-2 {{ request()->routeIs('admin.trends.google-trends') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                🔍 Google Trends
                            </a>
                        </div>
                    @endcan

                </div>

                <!-- Admin -->
                <div class="space-y-1 border-t border-slate-800 pt-4">
                    <p class="px-3 py-2 text-xs font-semibold text-slate-500 uppercase tracking-wider">Administração</p>
                    <a href="{{ route('admin.settings') }}"
                       class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.settings') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        ⚙️ Configurações
                    </a>

                    @can('audit.view')
                        <a href="{{ route('admin.audit.index') }}"
                           class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.audit.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            🔐 Auditoria
                        </a>
                    @endcan

                    <a href="{{ route('admin.guide') }}"
                       class="block rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.guide') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        📖 Guia
                    </a>
                </div>
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
                <div class="flex items-center gap-2">
                    <h1 class="text-lg font-semibold text-white">{{ $header ?? '' }}</h1>
                    {{ $help ?? '' }}
                </div>
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
