<?php

namespace JmfSystem\CustomerIntelligence;

use Illuminate\Contracts\Http\Kernel as KernelContract;
use Illuminate\Foundation\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use JmfSystem\CustomerIntelligence\Http\Middleware\ResolveVisitorAndSession;
use JmfSystem\CustomerIntelligence\Support\VisitorContext;

class CustomerIntelligenceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/customer-intelligence.php', 'customer-intelligence');

        // scoped() em vez de singleton(): garante estado limpo por requisição
        // mesmo sob servidores de longa duração (ex.: Laravel Octane), onde um
        // singleton tradicional vazaria o visitor/session de uma requisição
        // para a próxima.
        $this->app->scoped(VisitorContext::class);
        $this->app->scoped(Client::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/customer-intelligence.php' => config_path('customer-intelligence.php'),
        ], 'customer-intelligence-config');

        // appendMiddlewareToGroup precisa ser chamado no Kernel (não no
        // Router::pushMiddlewareToGroup!): o Kernel mantém sua própria cópia
        // de $middlewareGroups e a resincroniza para o Router a cada
        // requisição — um push feito direto no Router seria sobrescrito por
        // essa resincronização, apagando o middleware do pacote
        // silenciosamente. Resolvido via make() (não via type-hint do
        // parâmetro de boot()) para garantir que é exatamente a instância de
        // Kernel já registrada pela aplicação, e não uma nova instância
        // desacoplada do ciclo de vida real da requisição.
        /** @var Kernel $kernel */
        $kernel = $this->app->make(KernelContract::class);
        $kernel->appendMiddlewareToGroup('web', ResolveVisitorAndSession::class);
    }
}
