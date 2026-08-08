<?php

use App\Http\Controllers\Api\EventIngestController;
use App\Http\Controllers\Api\IdentifyContactController;
use App\Http\Controllers\Api\ListContactEventsController;
use App\Http\Controllers\Api\ListContactsController;
use App\Http\Controllers\Api\ListEventsController;
use App\Http\Controllers\Api\Marketplace\CustomerJourneyController;
use App\Http\Controllers\Api\Marketplace\SellerAnalyticsController;
use App\Http\Controllers\Api\Marketplace\SellerRecommendationsController;
use App\Http\Controllers\Api\Marketplace\TopProductsController;
use App\Http\Controllers\Api\MetricsController;
use App\Http\Controllers\Api\OpportunitiesController;
use App\Http\Controllers\Api\PingController;
use App\Http\Controllers\Api\RecommendationsController;
use App\Http\Controllers\Api\ShowContactController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'ensure.application.active'])
    ->prefix('v1')
    ->group(function (): void {
        Route::middleware('throttle:api-application')->group(function (): void {
            Route::get('/ping', PingController::class)->name('api.ping');
            Route::post('/contacts/identify', IdentifyContactController::class)->name('api.contacts.identify');
            Route::get('/recommendations', RecommendationsController::class)->name('api.recommendations.index');
            Route::get('/opportunities/{type}', OpportunitiesController::class)->name('api.opportunities.index');
            Route::get('/metrics', MetricsController::class)->name('api.metrics.index');
            Route::get('/events', ListEventsController::class)->name('api.events.index');
            Route::get('/contacts', ListContactsController::class)->name('api.contacts.index');
            Route::get('/contacts/{contact}', ShowContactController::class)->name('api.contacts.show');
            Route::get('/contacts/{contact}/events', ListContactEventsController::class)->name('api.contacts.events');
        });

        Route::middleware('throttle:api-events')->group(function (): void {
            Route::post('/events', EventIngestController::class)->name('api.events.store');
        });

        Route::prefix('marketplace')->group(function (): void {
            Route::middleware('throttle:api-application')->group(function (): void {
                Route::get('/sellers/{seller_id}/analytics', SellerAnalyticsController::class)->name('api.marketplace.seller.analytics');
                Route::get('/sellers/{seller_id}/recommendations', SellerRecommendationsController::class)->name('api.marketplace.seller.recommendations');
                Route::get('/products/top', TopProductsController::class)->name('api.marketplace.products.top');
                Route::get('/journey/{contact_id}', CustomerJourneyController::class)->name('api.marketplace.journey');
            });
        });
    });
