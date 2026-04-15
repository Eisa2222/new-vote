<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Module routes are auto-loaded by ModulesServiceProvider under the "api" prefix.
Route::middleware('auth:sanctum')->get('/user', fn (Request $r) => $r->user());
