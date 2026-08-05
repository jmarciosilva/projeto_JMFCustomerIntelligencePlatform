<?php

use App\Http\Controllers\Api\EventIngestController;
use App\Http\Controllers\Api\IdentifyContactController;
use App\Http\Controllers\Api\PingController;
use App\Http\Controllers\Api\RecommendationsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'ensure.application.active'])
    ->prefix('v1')
    ->group(function (): void {
        Route::middleware('throttle:api-application')->group(function (): void {
            Route::get('/ping', PingController::class)->name('api.ping');
            Route::post('/contacts/identify', IdentifyContactController::class)->name('api.contacts.identify');
            Route::get('/recommendations', RecommendationsController::class)->name('api.recommendations.index');
        });

        Route::middleware('throttle:api-events')->group(function (): void {
            Route::post('/events', EventIngestController::class)->name('api.events.store');
        });
    });
