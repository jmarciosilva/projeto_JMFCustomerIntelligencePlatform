<?php

namespace App\Providers;

use App\Models\Application;
use App\Models\Contact;
use App\Models\Tenant;
use App\Models\User;
use App\Policies\ApplicationPolicy;
use App\Policies\ContactPolicy;
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
        Gate::policy(Contact::class, ContactPolicy::class);

        // Limite conservador por aplicação autenticada, usado por endpoints de
        // baixo volume (ex.: /api/v1/ping).
        RateLimiter::for('api-application', function ($request) {
            return Limit::perMinute(60)->by($request->user()?->id);
        });

        // Limite mais generoso para a ingestão de eventos (Fase 04), que tende a
        // ter volume bem maior que os demais endpoints da API.
        RateLimiter::for('api-events', function ($request) {
            return Limit::perMinute(300)->by($request->user()?->id);
        });
    }
}
