<?php

use App\Http\Controllers\Admin\LogoutController;
use App\Http\Controllers\Affiliate\LinkRedirectController;
use App\Http\Controllers\StatusController;
use App\Livewire\Admin\Affiliate\ProductOpportunitiesIndex;
use App\Livewire\Admin\Affiliate\AnalyticsDashboard as AffiliateAnalyticsDashboard;
use App\Livewire\Admin\Affiliate\RecommendationDashboard as AffiliateRecommendationDashboard;
use App\Livewire\Admin\Affiliate\LabGuide;
use App\Livewire\Admin\Affiliate\ProductSuggestionsByTrends;
use App\Livewire\Admin\Affiliate\AffiliateLinkForm;
use App\Livewire\Admin\Affiliate\AffiliateLinkIndex;
use App\Livewire\Admin\Affiliate\CampaignForm;
use App\Livewire\Admin\Affiliate\CampaignIndex;
use App\Livewire\Admin\Affiliate\ConversionForm;
use App\Livewire\Admin\Affiliate\ConversionImport;
use App\Livewire\Admin\Affiliate\ConversionIndex;
use App\Livewire\Admin\Affiliate\ContentPublicationForm;
use App\Livewire\Admin\Affiliate\ContentPublicationIndex;
use App\Livewire\Admin\Affiliate\ProductForm as AffiliateProductForm;
use App\Livewire\Admin\Affiliate\ProductImport as AffiliateProductImport;
use App\Livewire\Admin\Affiliate\ProductIndex as AffiliateProductIndex;
use App\Livewire\Admin\Affiliate\ProgramForm as AffiliateProgramForm;
use App\Livewire\Admin\Affiliate\ProgramIndex as AffiliateProgramIndex;
use App\Livewire\Admin\Analytics\AnalyticsDashboard;
use App\Livewire\Admin\Applications\ApplicationForm;
use App\Livewire\Admin\Applications\ApplicationIndex;
use App\Livewire\Admin\Applications\ApplicationTokens;
use App\Livewire\Admin\Audit\AuditLogIndex;
use App\Livewire\Admin\Contacts\ContactIndex;
use App\Livewire\Admin\Contacts\ContactShow;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Intelligence\BusinessIntelligenceDashboard;
use App\Livewire\Admin\Intelligence\RecommendationsDashboard;
use App\Livewire\Admin\Marketing\ContentDashboard;
use App\Livewire\Admin\Profile;
use App\Livewire\Admin\Settings\PlatformSettingsForm;
use App\Livewire\Admin\Tenants\TenantForm;
use App\Livewire\Admin\Tenants\TenantIndex;
use App\Livewire\Admin\Trends\TrendingTopicsImporter;
use App\Livewire\Admin\Trends\TrendShow;
use App\Livewire\Admin\Trends\WatchlistForm;
use App\Livewire\Admin\Trends\WatchlistIndex;
use App\Livewire\Admin\Trends\WatchlistShow;
use App\Livewire\Admin\UserGuide;
use App\Livewire\Admin\Users\UserForm;
use App\Livewire\Admin\Users\UserIndex;
use App\Livewire\Auth\Login;
use App\Livewire\Marketplace\ContactsList;
use App\Livewire\Marketplace\CustomerJourneyTimeline;
use App\Livewire\Marketplace\Dashboard as MarketplaceDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', StatusController::class)->name('status');

// Rota pública para redirecionamento de links de afiliados (Fase 27)
Route::get('/go/{slug}', [LinkRedirectController::class, 'redirect'])->name('affiliate.redirect');

Route::middleware('guest')->group(function (): void {
    Route::get('/admin/login', Login::class)
        ->middleware('throttle:10,1')
        ->name('admin.login');
});

Route::middleware(['auth', 'ensure.active'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', Dashboard::class)->name('dashboard');

    Route::get('/users', UserIndex::class)->name('users.index');
    Route::get('/users/create', UserForm::class)->name('users.create');
    Route::get('/users/{user}/edit', UserForm::class)->name('users.edit');

    Route::get('/tenants', TenantIndex::class)->name('tenants.index');
    Route::get('/tenants/create', TenantForm::class)->name('tenants.create');
    Route::get('/tenants/{tenant}/edit', TenantForm::class)->name('tenants.edit');

    Route::get('/applications', ApplicationIndex::class)->name('applications.index');
    Route::get('/applications/create', ApplicationForm::class)->name('applications.create');
    Route::get('/applications/{application}/edit', ApplicationForm::class)->name('applications.edit');
    Route::get('/applications/{application}/tokens', ApplicationTokens::class)->name('applications.tokens');

    Route::get('/analytics', AnalyticsDashboard::class)->name('analytics.index');

    // Marketplace & Seller Analytics
    Route::prefix('marketplace')->name('marketplace.')->group(function (): void {
        Route::get('/', MarketplaceDashboard::class)->name('dashboard');
        Route::get('/contacts', ContactsList::class)->name('contacts');
        Route::get('/contacts/{contact}', CustomerJourneyTimeline::class)->name('journey');
    });

    // Fase 13 — AI Business Intelligence / Fase 14 — AI Business Assistant
    Route::prefix('intelligence')->name('intelligence.')->group(function (): void {
        Route::get('/', BusinessIntelligenceDashboard::class)->name('dashboard');
        Route::get('/recommendations', RecommendationsDashboard::class)->name('recommendations');
    });

    // Fase 15 — AI Marketing
    Route::get('/marketing', ContentDashboard::class)->name('marketing.dashboard');

    // Fase 22 — Affiliate Intelligence / Fase 27 — Content & Link Tracking
    Route::prefix('affiliate')->name('affiliate.')->group(function (): void {
        Route::get('/programs', AffiliateProgramIndex::class)->name('programs.index');
        Route::get('/programs/create', AffiliateProgramForm::class)->name('programs.create');
        Route::get('/programs/{program}/edit', AffiliateProgramForm::class)->name('programs.edit');

        Route::get('/products', AffiliateProductIndex::class)->name('products.index');
        Route::get('/products/import', AffiliateProductImport::class)->name('products.import');
        Route::get('/products/suggestions', ProductSuggestionsByTrends::class)->name('products.suggestions');
        Route::get('/products/create', AffiliateProductForm::class)->name('products.create');
        Route::get('/products/{product}/edit', AffiliateProductForm::class)->name('products.edit');

        // Fase 27 — Content & Link Tracking
        Route::get('/campaigns', CampaignIndex::class)->name('campaigns.index');
        Route::get('/campaigns/create', CampaignForm::class)->name('campaigns.create');
        Route::get('/campaigns/{campaign}/edit', CampaignForm::class)->name('campaigns.edit');

        Route::get('/content', ContentPublicationIndex::class)->name('content.index');
        Route::get('/content/create', ContentPublicationForm::class)->name('content.create');
        Route::get('/content/{content}/edit', ContentPublicationForm::class)->name('content.edit');

        Route::get('/links', AffiliateLinkIndex::class)->name('links.index');
        Route::get('/links/create', AffiliateLinkForm::class)->name('links.create');
        Route::get('/links/{link}/edit', AffiliateLinkForm::class)->name('links.edit');

        Route::get('/conversions', ConversionIndex::class)->name('conversions.index');
        Route::get('/conversions/create', ConversionForm::class)->name('conversions.create');
        Route::get('/conversions/{conversion}/edit', ConversionForm::class)->name('conversions.edit');
        Route::get('/conversions/import', ConversionImport::class)->name('conversions.import');

        Route::get('/analytics', AffiliateAnalyticsDashboard::class)->name('analytics.index');

        Route::get('/recommendations', AffiliateRecommendationDashboard::class)->name('recommendations.index');

        Route::get('/guide', LabGuide::class)->name('guide');

        // Sprint A — Etapa A4: UI & Admin Panels
        Route::get('/product-opportunities', ProductOpportunitiesIndex::class)->name('product-opportunities.index');
    });

    // Fase 23 — Trend Intelligence
    Route::prefix('trends')->name('trends.')->group(function (): void {
        Route::get('/watchlists', WatchlistIndex::class)->name('watchlists.index');
        Route::get('/watchlists/create', WatchlistForm::class)->name('watchlists.create');
        Route::get('/watchlists/{watchlist}/edit', WatchlistForm::class)->name('watchlists.edit');
        Route::get('/watchlists/{watchlist}', WatchlistShow::class)->name('watchlists.show');
        Route::get('/google-trends', TrendingTopicsImporter::class)->name('google-trends');
        Route::get('/{trend}', TrendShow::class)->name('show');
    });

    Route::get('/contacts', ContactIndex::class)->name('contacts.index');
    Route::get('/contacts/{contact}', ContactShow::class)->name('contacts.show');

    Route::get('/auditoria', AuditLogIndex::class)->name('audit.index');

    Route::get('/perfil', Profile::class)->name('profile');

    Route::get('/configuracoes', PlatformSettingsForm::class)->name('settings');

    Route::get('/guia', UserGuide::class)->name('guide');

    Route::post('/logout', LogoutController::class)->name('logout');

    // Plugin: JMF Customer Intelligence
    Route::prefix('plugin/jmf-ci')->name('plugin.jmf-ci.')->group(function (): void {
        require base_path('vendor/jmf-system/customer-intelligence-sdk/src/Routes/plugin.php');
    });
});
