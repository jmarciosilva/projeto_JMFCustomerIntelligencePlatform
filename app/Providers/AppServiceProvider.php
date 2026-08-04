<?php

namespace App\Providers;

use App\Models\Application;
use App\Models\Tenant;
use App\Models\User;
use App\Policies\ApplicationPolicy;
use App\Policies\TenantPolicy;
use App\Policies\UserPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // A role "Super Admin" recebe todas as permissions via RolePermissionSeeder
        // (ao invés de um Gate::before global) para que o auto-check de exclusão em
        // UserPolicy::delete (impedir que um admin exclua a própria conta) também
        // se aplique a Super Admins.
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Tenant::class, TenantPolicy::class);
        Gate::policy(Application::class, ApplicationPolicy::class);

        // Limite conservador por aplicação autenticada; a Fase 04 pode ajustar
        // por endpoint conforme a necessidade real de ingestão de eventos.
        RateLimiter::for('api-application', function ($request) {
            return Limit::perMinute(60)->by($request->user()?->id);
        });
    }
}
