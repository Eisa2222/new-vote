<?php

use App\Http\Controllers\Admin\AdminCampaignController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminClubController;
use App\Http\Controllers\Admin\AdminPlayerController;
use App\Http\Controllers\Admin\AdminResultController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\AdminTeamOfSeasonController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/login'));

Route::middleware('guest')->group(function () {
    Route::get('login',  [LoginController::class, 'show'])->name('login');
    Route::post('login', [LoginController::class, 'store']);

     // Password Reset
    Route::get('forgot-password',         [ForgotPasswordController::class, 'show'])->name('password.request');
    Route::post('forgot-password',        [ForgotPasswordController::class, 'store'])->name('password.email');
    
    Route::get('reset-password/{token}',  [ResetPasswordController::class, 'show'])->name('password.reset');
    Route::post('reset-password',         [ResetPasswordController::class, 'store'])->name('password.update');

});

Route::post('logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware(['web', 'auth'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/', function () {
        $user = auth()->user();
        //
        if ($user && $user->hasRole('committee') && ! $user->can('users.manage'))
            return redirect('/admin/campaigns');
        if ($user && $user->hasRole('campaign_manager') && ! $user->can('users.manage'))
            return redirect('/admin/campaigns');
        //
        return view('admin.dashboard');
    });

    // Clubs
    Route::prefix('clubs')->name('clubs.')->group(function () {
        Route::get('export',          [AdminClubController::class, 'export']);
        Route::get('export/template', [AdminClubController::class, 'exportTemplate']);
        Route::post('import',         [AdminClubController::class, 'import']);
        Route::post('{club}/toggle',  [AdminClubController::class, 'toggle']);
    });
    Route::resource('clubs', AdminClubController::class);


    // Players
    Route::prefix('players')->name('players.')->group(function () {
        Route::get('export',          [AdminPlayerController::class, 'export']);
        Route::get('export/template', [AdminPlayerController::class, 'exportTemplate']);
        Route::post('import',         [AdminPlayerController::class, 'import']);
    });
    Route::resource('players', AdminPlayerController::class)->except(['show']);


    // Campaigns
    Route::resource('campaigns', AdminCampaignController::class);
    Route::prefix('campaigns/{campaign}')->name('campaigns.')->group(function () {
        Route::get('stats',            [AdminCampaignController::class, 'stats']);
        Route::post('submit-approval', [AdminCampaignController::class, 'submitForApproval']);
        Route::post('approve',         [AdminCampaignController::class, 'approve']);
        Route::post('reject',          [AdminCampaignController::class, 'reject']);
        Route::post('publish',         [AdminCampaignController::class, 'publish']);
        Route::post('activate',        [AdminCampaignController::class, 'activate']);
        Route::post('close',           [AdminCampaignController::class, 'close']);
        Route::post('archive',         [AdminCampaignController::class, 'archive']);
    });

    // Categories
    Route::prefix('campaigns/{campaign}/categories')->name('categories.')->group(function () {
        Route::get('',  [AdminCategoryController::class, 'index']);
        Route::post('', [AdminCategoryController::class, 'store']);
    });
    Route::prefix('categories/{category}')->name('categories.')->group(function () {
        Route::put('',              [AdminCategoryController::class, 'update']);
        Route::delete('',           [AdminCategoryController::class, 'destroy']);
        Route::post('candidates',   [AdminCategoryController::class, 'storeCandidate']);
    });
    Route::delete('candidates/{candidate}', [AdminCategoryController::class, 'destroyCandidate'])->name('candidates.destroy');


    // Team of the Season
    Route::prefix('tos')->name('tos.')->group(function () {
        Route::get('create',                 [AdminTeamOfSeasonController::class, 'create']);
        Route::post('',                      [AdminTeamOfSeasonController::class, 'store']);
        Route::get('{campaign}/candidates',  [AdminTeamOfSeasonController::class, 'candidates']);
        Route::post('{campaign}/candidates', [AdminTeamOfSeasonController::class, 'attachCandidates']);
    });


    // Results
    Route::prefix('results')->name('results.')->group(function () {
        Route::get('',                      [AdminResultController::class, 'index'])->name('index');
        Route::get('{campaign}',            [AdminResultController::class, 'show'])->name('show');
        Route::post('{campaign}/calculate', [AdminResultController::class, 'calculate'])->name('calculate');
        Route::post('approve/{result}',     [AdminResultController::class, 'approve'])->name('approve');
        Route::post('hide/{result}',        [AdminResultController::class, 'hide'])->name('hide');
        Route::post('announce/{result}',    [AdminResultController::class, 'announce'])->name('announce');
        Route::post('{result}/resolve-tie', [AdminResultController::class, 'resolveTie'])->name('resolveTie');
    });


    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('',                       [AdminSettingsController::class, 'index'])->name('index');
        Route::post('general',               [AdminSettingsController::class, 'updateGeneral']);
        Route::post('sports',                [AdminSettingsController::class, 'storeSport']);
        Route::put('sports/{sport}',         [AdminSettingsController::class, 'updateSport']);
        Route::delete('sports/{sport}',      [AdminSettingsController::class, 'destroySport']);
        Route::post('leagues',               [AdminSettingsController::class, 'storeLeague']);
        Route::delete('leagues/{league}',    [AdminSettingsController::class, 'destroyLeague']);
        Route::get('leagues/{league}/clubs', [AdminSettingsController::class, 'leagueClubs']);
    });


    // Users
    Route::prefix('users')->name('users.')->group(function () {
        Route::post('{user}/toggle', [AdminUserController::class, 'toggle'])->name('toggle');
    });
    Route::resource('users', AdminUserController::class);
});

Route::middleware('web')->get('/set-locale/{locale}', function (string $locale) {
    if (in_array($locale, ['ar', 'en'], true)) {
        session(['locale' => $locale]);
        app()->setLocale($locale);
    }
    return back();
});
