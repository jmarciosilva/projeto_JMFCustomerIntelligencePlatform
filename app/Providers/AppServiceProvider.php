<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
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
    }
}
