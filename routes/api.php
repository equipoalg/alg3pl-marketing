<?php

use App\Http\Controllers\Api\LeadApiController;
use App\Http\Controllers\Api\MetricsApiController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Middleware\ApiTokenAuth;
use Illuminate\Support\Facades\Route;

// Webhooks (no API token — use their own secret)
Route::prefix('v1/webhook')->group(function () {
    Route::post('/fluent-forms', [WebhookController::class, 'fluentForms']);
});

Route::prefix('v1')->middleware(ApiTokenAuth::class)->group(function () {

    // Leads
    Route::get('/leads', [LeadApiController::class, 'index']);
    Route::post('/leads', [LeadApiController::class, 'store']);
    Route::get('/leads/{lead}', [LeadApiController::class, 'show']);
    Route::patch('/leads/{lead}', [LeadApiController::class, 'update']);

    // Metrics
    Route::get('/metrics/shipments', [MetricsApiController::class, 'shipments']);
    Route::post('/metrics/shipments', [MetricsApiController::class, 'storeShipment']);
    Route::get('/metrics/sales', [MetricsApiController::class, 'sales']);
    Route::get('/metrics/sustainability', [MetricsApiController::class, 'sustainability']);
});
