<?php

use App\Modules\Campaigns\Http\Controllers\CampaignController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('campaigns', CampaignController::class);
    Route::post('campaigns/{campaign}/publish', [CampaignController::class, 'publish']);
    Route::post('campaigns/{campaign}/close',   [CampaignController::class, 'close']);
});
