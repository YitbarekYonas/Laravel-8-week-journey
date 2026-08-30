<?php

use App\Http\Controllers\CasualGreetingController;
use App\Http\Controllers\DeferredDemoController;
use App\Http\Controllers\FacadeDemoController;
use App\Http\Controllers\GreetingController;
use App\Http\Controllers\IdDemoController;
use Illuminate\Support\Facades\Route;

// Day 1/2 — global bind(), contextual binding, singleton vs bind()
Route::get('/greet/{name}', [GreetingController::class, 'greet']);
Route::get('/greet-casual/{name}', [CasualGreetingController::class, 'greet']);
Route::get('/ids/demo', [IdDemoController::class, 'demonstrate']);

// Day 3 — deferred provider: watch ReportServiceProvider go from
// "never loaded" to "loaded" based purely on whether its binding has
// been resolved yet.
Route::get('/deferred/status', [DeferredDemoController::class, 'status']);
Route::get('/deferred/generate', [DeferredDemoController::class, 'generate']);
Route::get('/deferred/reset', [DeferredDemoController::class, 'reset']);

// Day 3 — facade internals: compare this route's resolved implementation
// against GET /api/greet/{name}'s to see contextual binding NOT applying
// to a facade's bare static call.
Route::get('/greet-facade/{name}', [FacadeDemoController::class, 'greet']);
