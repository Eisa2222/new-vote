<?php

use App\Modules\Results\Http\Controllers\ResultController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::get('campaigns/{campaign}/result',                  [ResultController::class, 'show']);
    Route::post('campaigns/{campaign}/result/calculate',       [ResultController::class, 'calculate']);
    Route::get('campaigns/{campaign}/team-of-the-season',      [ResultController::class, 'teamOfTheSeason']);
    Route::post('results/{result}/approve',                    [ResultController::class, 'approve']);
    Route::post('results/{result}/hide',                       [ResultController::class, 'hide']);
    Route::post('results/{result}/announce',                   [ResultController::class, 'announce']);
});
