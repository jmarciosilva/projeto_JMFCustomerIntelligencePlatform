<?php

use App\Http\Controllers\Api\PingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'ensure.application.active', 'throttle:api-application'])
    ->prefix('v1')
    ->group(function (): void {
        Route::get('/ping', PingController::class)->name('api.ping');
    });
