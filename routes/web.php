<?php

use App\Http\Controllers\Admin\LogoutController;
use App\Http\Controllers\StatusController;
use App\Livewire\Admin\Audit\AuditLogIndex;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Profile;
use App\Livewire\Admin\Users\UserForm;
use App\Livewire\Admin\Users\UserIndex;
use App\Livewire\Auth\Login;
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

    Route::get('/auditoria', AuditLogIndex::class)->name('audit.index');

    Route::get('/perfil', Profile::class)->name('profile');

    Route::post('/logout', LogoutController::class)->name('logout');
});
