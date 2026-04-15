<?php

use App\Modules\Players\Http\Controllers\PlayerController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('players', PlayerController::class);
});
