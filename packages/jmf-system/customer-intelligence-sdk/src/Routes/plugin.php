<?php

use Illuminate\Support\Facades\Route;
use JmfSystem\CustomerIntelligence\Livewire\Plugins\JmfCi\Dashboard;
use JmfSystem\CustomerIntelligence\Livewire\Plugins\JmfCi\Configuration;
use JmfSystem\CustomerIntelligence\Livewire\Plugins\JmfCi\Contacts\ContactIndex;
use JmfSystem\CustomerIntelligence\Livewire\Plugins\JmfCi\Contacts\ContactShow;
use JmfSystem\CustomerIntelligence\Livewire\Plugins\JmfCi\Events\EventIndex;

/**
 * Rotas do Plugin JMF Customer Intelligence.
 *
 * Para usar estas rotas, importe-as no seu arquivo routes/web.php:
 *
 *   Route::middleware(['auth', 'admin'])->group(function () {
 *       require base_path('vendor/jmf-system/customer-intelligence-sdk/src/Routes/plugin.php');
 *   });
 *
 * Ou use o JmfCiPluginRouteServiceProvider que faz isso automaticamente.
 */

Route::prefix('admin/plugin/jmf-ci')
    ->name('jmf-ci.')
    ->group(function () {
        // Dashboard
        Route::get('/', Dashboard::class)
            ->name('dashboard');

        // Configuration
        Route::get('configuration', Configuration::class)
            ->name('configuration');

        // Contacts
        Route::prefix('contacts')
            ->name('contacts.')
            ->group(function () {
                Route::get('/', ContactIndex::class)
                    ->name('index');

                Route::get('{contact}', ContactShow::class)
                    ->name('show');
            });

        // Events
        Route::get('events', EventIndex::class)
            ->name('events.index');
    });
