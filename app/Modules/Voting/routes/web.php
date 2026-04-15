<?php

use App\Modules\Voting\Http\Controllers\PublicVoteController;
use Illuminate\Support\Facades\Route;

Route::prefix('vote')->group(function () {
    Route::get('{token}',         [PublicVoteController::class, 'show'])->name('voting.show');
    Route::post('{token}',        [PublicVoteController::class, 'submit'])->name('voting.submit');
    Route::get('{token}/thanks',  [PublicVoteController::class, 'thanks'])->name('voting.thanks');
});
