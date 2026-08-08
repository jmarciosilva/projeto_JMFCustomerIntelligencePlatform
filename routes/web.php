<?php

use App\Http\Controllers\Admin\LogoutController;
use App\Http\Controllers\StatusController;
use App\Livewire\Admin\Analytics\AnalyticsDashboard;
use App\Livewire\Admin\Applications\ApplicationForm;
use App\Livewire\Admin\Applications\ApplicationIndex;
use App\Livewire\Admin\Applications\ApplicationTokens;
use App\Livewire\Admin\Audit\AuditLogIndex;
use App\Livewire\Admin\Contacts\ContactIndex;
use App\Livewire\Admin\Contacts\ContactShow;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Profile;
use App\Livewire\Admin\Tenants\TenantForm;
use App\Livewire\Admin\Tenants\TenantIndex;
use App\Livewire\Admin\UserGuide;
use App\Livewire\Admin\Users\UserForm;
use App\Livewire\Admin\Users\UserIndex;
use App\Livewire\Auth\Login;
use App\Livewire\Marketplace\Dashboard as MarketplaceDashboard;
use App\Livewire\Marketplace\ContactsList;
use App\Livewire\Marketplace\CustomerJourneyTimeline;
use App\Models\Application;
use App\Models\Contact;
use Illuminate\Support\Facades\Route;

Route::get('/', StatusController::class)->name('status');

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
        Route::get('/', function () {
            $application = auth()->user()->application ?? Application::first();
            return view('admin.marketplace.dashboard', [
                'application' => $application,
            ]);
        })->name('dashboard');

        Route::get('/contacts', function () {
            $tenant = auth()->user()->tenant ?? \App\Models\Tenant::first();
            return view('admin.marketplace.contacts', [
                'tenant' => $tenant,
            ]);
        })->name('contacts');

        Route::get('/contacts/{contact}', function (Contact $contact) {
            return view('admin.marketplace.journey', [
                'contact' => $contact,
            ]);
        })->name('journey');
    });

    Route::get('/contacts', ContactIndex::class)->name('contacts.index');
    Route::get('/contacts/{contact}', ContactShow::class)->name('contacts.show');

    Route::get('/auditoria', AuditLogIndex::class)->name('audit.index');

    Route::get('/perfil', Profile::class)->name('profile');

    Route::get('/guia', UserGuide::class)->name('guide');

    Route::post('/logout', LogoutController::class)->name('logout');

    // Plugin: JMF Customer Intelligence
    Route::prefix('plugin/jmf-ci')->name('plugin.jmf-ci.')->group(function (): void {
        require base_path('vendor/jmf-system/customer-intelligence-sdk/src/Routes/plugin.php');
    });
});
