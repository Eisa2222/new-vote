<?php

use App\Modules\Sports\Http\Controllers\SportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::get('sports', [SportController::class, 'index']);
});
