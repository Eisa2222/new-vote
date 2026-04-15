<?php

use App\Modules\Clubs\Http\Controllers\ClubController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('clubs', ClubController::class);
});
