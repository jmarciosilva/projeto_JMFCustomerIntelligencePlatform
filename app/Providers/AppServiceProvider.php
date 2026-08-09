<?php

namespace App\Providers;

use App\Domain\Affiliate\Contracts\AffiliateProviderInterface;
use App\Domain\Affiliate\ManualAffiliateProvider;
use App\Domain\Marketing\AnthropicContentGenerator;
use App\Domain\Marketing\Contracts\ContentGenerator;
use App\Domain\Marketing\TemplateContentGenerator;
use App\Models\AffiliateProduct;
use App\Models\AffiliateProgram;
use App\Models\Application;
use App\Models\Contact;
use App\Models\Tenant;
use App\Models\User;
use App\Policies\AffiliateProductPolicy;
use App\Policies\AffiliateProgramPolicy;
use App\Policies\ApplicationPolicy;
use App\Policies\ContactPolicy;
use App\Policies\TenantPolicy;
use App\Policies\UserPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use JmfSystem\CustomerIntelligence\Livewire\Plugins\JmfCi\Configuration;
use JmfSystem\CustomerIntelligence\Livewire\Plugins\JmfCi\Contacts\ContactIndex;
use JmfSystem\CustomerIntelligence\Livewire\Plugins\JmfCi\Contacts\ContactShow;
use JmfSystem\CustomerIntelligence\Livewire\Plugins\JmfCi\Dashboard;
use JmfSystem\CustomerIntelligence\Livewire\Plugins\JmfCi\Events\EventIndex;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ContentGenerator::class, function () {
            return match (config('marketing.driver')) {
                'anthropic' => new AnthropicContentGenerator(
                    config('marketing.anthropic.api_key') ?? '',
                    config('marketing.anthropic.model'),
                    config('marketing.anthropic.base_url'),
                    config('marketing.anthropic.max_tokens'),
                ),
                default => new TemplateContentGenerator,
            };
        });

        // Único provider real disponível no MVP da Fase 22 (ver README.md/ROADMAP.md):
        // programas sem API oficial documentada usam cadastro manual/import CSV.
        $this->app->bind(AffiliateProviderInterface::class, ManualAffiliateProvider::class);
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
        Gate::policy(AffiliateProgram::class, AffiliateProgramPolicy::class);
        Gate::policy(AffiliateProduct::class, AffiliateProductPolicy::class);

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

        $this->registerJmfCiComponents();
    }

    private function registerJmfCiComponents(): void
    {
        // Publicar views do SDK
        $this->publishes([
            __DIR__.'/../../vendor/jmf-system/customer-intelligence-sdk/resources/views/plugins/jmf-ci' => resource_path('views/plugins/jmf-ci'),
        ], 'jmf-ci-views');

        // Registrar componentes Livewire
        Livewire::component('jmf-ci.dashboard', Dashboard::class);
        Livewire::component('jmf-ci.configuration', Configuration::class);
        Livewire::component('jmf-ci.contacts.index', ContactIndex::class);
        Livewire::component('jmf-ci.contacts.show', ContactShow::class);
        Livewire::component('jmf-ci.events.index', EventIndex::class);

        // Registrar caminho de views do SDK
        view()->addLocation(__DIR__.'/../../vendor/jmf-system/customer-intelligence-sdk/resources/views');
    }
}
