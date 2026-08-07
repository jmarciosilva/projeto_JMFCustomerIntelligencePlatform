<?php

namespace JmfSystem\CustomerIntelligence\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class JmfCiPluginRouteServiceProvider extends ServiceProvider
{
    /**
     * Define as rotas para o plugin.
     *
     * Este ServiceProvider registra automaticamente as rotas do plugin JMF CI.
     * Para usar, adicione ao seu config/app.php em 'providers':
     *
     *   JmfSystem\CustomerIntelligence\Providers\JmfCiPluginRouteServiceProvider::class,
     *
     * Ou registre manualmente em routes/web.php:
     *
     *   Route::middleware(['auth', 'admin'])->group(function () {
     *       require base_path('vendor/jmf-system/customer-intelligence-sdk/src/Routes/plugin.php');
     *   });
     */
    public function boot(): void
    {
        $this->routes(function () {
            Route::middleware(['web', 'auth'])
                ->prefix('admin/plugin/jmf-ci')
                ->name('jmf-ci.')
                ->group(base_path('vendor/jmf-system/customer-intelligence-sdk/src/Routes/plugin.php'));
        });
    }
}
