<?php

use App\Http\Controllers\CasualGreetingController;
use App\Http\Controllers\ConfigDemoController;
use App\Http\Controllers\DeferredDemoController;
use App\Http\Controllers\FacadeDemoController;
use App\Http\Controllers\GreetingController;
use App\Http\Controllers\GreetingStrategyController;
use App\Http\Controllers\GreetOptionalController;
use App\Http\Controllers\HeaderDemoController;
use App\Http\Controllers\IdDemoController;
use App\Http\Controllers\InputAccessDemoController;
use App\Http\Controllers\LifecycleController;
use App\Http\Controllers\RouteLinksController;
use App\Http\Controllers\StatusCodeDemoController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskSummaryController;
use Illuminate\Support\Facades\Route;

// ═══════════════════════════════════════════════════════════════════════
// Week 1, Days 1–4 — demo routes, grouped and named (Week 1 Day 5).
// ═══════════════════════════════════════════════════════════════════════
Route::prefix('demo')->name('demo.')->group(function () {

    Route::get('/greet/{name}', [GreetingController::class, 'greet'])
        ->name('greet');

    Route::get('/greet-casual/{name}', [CasualGreetingController::class, 'greet'])
        ->name('greet-casual');

    Route::get('/greet-facade/{name}', [FacadeDemoController::class, 'greet'])
        ->name('greet-facade');

    Route::get('/ids', [IdDemoController::class, 'demonstrate'])
        ->name('ids');

    Route::prefix('deferred')->name('deferred.')->group(function () {
        Route::get('/status', [DeferredDemoController::class, 'status'])->name('status');
        Route::get('/generate', [DeferredDemoController::class, 'generate'])->name('generate');
        Route::get('/reset', [DeferredDemoController::class, 'reset'])->name('reset');
    });

    Route::get('/config', [ConfigDemoController::class, 'show'])
        ->name('config');
});

// ═══════════════════════════════════════════════════════════════════════
// Week 2, Day 1 — REAL resource routes, replacing Week 1 Day 5's manual
// prefix/group block. Route::apiResource() generates EXACTLY the route
// names that block was manually replicating by hand: tasks.index,
// tasks.store, tasks.show, tasks.update, tasks.destroy — over
// GET/POST/GET/PUT-PATCH/DELETE respectively, all under /tasks, all
// using {task} as the parameter name (matching what route model binding
// expects). create/edit are correctly omitted — this is a JSON API, not
// an HTML form-rendering app.
// ─ whereNumber() still applies to the resource's implicit {task}
// parameter, exactly as it did when this was written by hand.
Route::apiResource('tasks', TaskController::class)
    ->whereNumber('task');

// ── Week 2 Day 3 — genuine motivation for 409 Conflict ───────────────────
// A single-purpose state-transition action — PATCH (partial change),
// not PUT (full replace), matching the resource's semantics exactly.
Route::patch('/tasks/{task}/done', [TaskController::class, 'markAsDone'])
    ->whereNumber('task')
    ->name('tasks.markAsDone');

// ── A single-action controller — no [Controller::class, 'method'] array,
// just the class name directly. Laravel calls __invoke() automatically.
Route::get('/tasks-summary', TaskSummaryController::class)
    ->name('tasks.summary');

// ═══════════════════════════════════════════════════════════════════════
// Week 1, Day 5 concepts — optional parameter now served by a proper
// single-action controller (Week 2 Day 1), relocated out of
// TaskController now that TaskController is a real resource controller.
// ═══════════════════════════════════════════════════════════════════════
Route::get('/greet-optional/{name?}', GreetOptionalController::class)
    ->name('greet-optional');

Route::middleware('throttle:api-demo')->prefix('limited')->name('limited.')->group(function () {
    Route::get('/ping', function () {
        return response()->json([
            'message' => 'pong',
            'note' => 'This route is rate-limited via the api-demo limiter '
                .'(30 requests/minute/IP).',
        ]);
    })->name('ping');
});

Route::get('/route-links', [RouteLinksController::class, 'index'])
    ->name('route-links');

// ═══════════════════════════════════════════════════════════════════════
// Week 2, Day 2 — accessing input, every common way, side by side.
// ═══════════════════════════════════════════════════════════════════════
Route::post('/input-demo', [InputAccessDemoController::class, 'show'])
    ->name('input-demo');

// ═══════════════════════════════════════════════════════════════════════
// Week 2, Day 3 — responses: macros, status codes, headers.
// ═══════════════════════════════════════════════════════════════════════
Route::get('/status-demo', StatusCodeDemoController::class)
    ->name('status-demo');

Route::get('/header-demo', HeaderDemoController::class)
    ->name('header-demo');

// ═══════════════════════════════════════════════════════════════════════
// Week 2, Day 4 — a route protected by a CUSTOM middleware group.
// Both ApiKeyMiddleware (real gating logic) AND ResponseTimeLoggerMiddleware
// (terminable — logs AFTER the response is sent) apply to everything in
// this group, from the single 'secure-api' name defined in bootstrap/app.php.
// ═══════════════════════════════════════════════════════════════════════
Route::middleware('secure-api')->prefix('secure')->name('secure.')->group(function () {
    Route::get('/ping', function () {
        return response()->success(
            ['message' => 'You provided a valid X-Api-Key header.'],
            'Access granted.'
        );
    })->name('ping');
});

// ═══════════════════════════════════════════════════════════════════════
// Week 1, Day 6 — the request lifecycle, made observable.
// ═══════════════════════════════════════════════════════════════════════
Route::prefix('lifecycle')->name('lifecycle.')->group(function () {

    Route::get('/trace', [LifecycleController::class, 'trace'])
        ->middleware(['trace:Outer', 'trace:Inner'])
        ->name('trace');

    Route::get('/plain', [LifecycleController::class, 'plain'])
        ->name('plain');
});

// ═══════════════════════════════════════════════════════════════════════
// Week 1, Day 7 — Mini-Project: config-driven greeting strategy.
// ═══════════════════════════════════════════════════════════════════════
Route::get('/mini-project/greet/{name}', [GreetingStrategyController::class, 'greet'])
    ->middleware('trace:MiniProject')
    ->name('mini-project.greet');
