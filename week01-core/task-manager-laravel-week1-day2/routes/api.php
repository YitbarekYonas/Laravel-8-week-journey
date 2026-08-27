<?php

use App\Http\Controllers\CasualGreetingController;
use App\Http\Controllers\GreetingController;
use App\Http\Controllers\IdDemoController;
use Illuminate\Support\Facades\Route;

// Day 1's route — global bind(), formal tone.
Route::get('/greet/{name}', [GreetingController::class, 'greet']);

// Day 2 — contextual binding: identical constructor type-hint as
// GreetingController above, resolved to a DIFFERENT concrete class
// because of the ->when()->needs()->give() rules in AppServiceProvider.
Route::get('/greet-casual/{name}', [CasualGreetingController::class, 'greet']);

// Day 2 — singleton vs bind(), proven via three manual resolutions
// within one request.
Route::get('/ids/demo', [IdDemoController::class, 'demonstrate']);
