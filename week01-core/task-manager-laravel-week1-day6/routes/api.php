<?php

use App\Http\Controllers\CasualGreetingController;
use App\Http\Controllers\ConfigDemoController;
use App\Http\Controllers\DeferredDemoController;
use App\Http\Controllers\FacadeDemoController;
use App\Http\Controllers\GreetingController;
use App\Http\Controllers\IdDemoController;
use App\Http\Controllers\LifecycleController;
use App\Http\Controllers\RouteLinksController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

// ═══════════════════════════════════════════════════════════════════════
// Week 1, Days 1–4 — every demo route from earlier days, now grouped and
// NAMED. Grouping doesn't change what any of these routes DO — it changes
// how they're organized, and gives every one of them a stable name that
// route() can reference regardless of the actual URI underneath (see
// RouteLinksController).
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
// Week 1, Day 5 — the real routing lesson starts here: parameters,
// constraints, and the beginning of the actual Task Manager API surface.
// ═══════════════════════════════════════════════════════════════════════

Route::prefix('tasks')->name('tasks.')->group(function () {

    Route::get('/', [TaskController::class, 'index'])
        ->name('index');

    // ── A CONSTRAINED route parameter ────────────────────────────────────
    // ->whereNumber('task') requires this segment to be one or more
    // digits — anything else (GET /api/tasks/abc, /api/tasks/12-3) never
    // reaches TaskController::show() at all; Laravel returns 404 before
    // the controller is even instantiated. The parameter name "task"
    // (not "id") is deliberate — it's the exact convention Eloquent route
    // model binding expects once a real Task model exists (Week 3), so
    // this route won't need renaming later.
    Route::get('/{task}', [TaskController::class, 'show'])
        ->whereNumber('task')
        ->name('show');
});

// ── An OPTIONAL route parameter ─────────────────────────────────────────
// The trailing ? makes {name} optional — this ONE route matches both
// GET /api/greet-optional and GET /api/greet-optional/Sam. See
// TaskController::greetOptional() for how the PHP-level default fills the
// gap when the segment is absent.
Route::get('/greet-optional/{name?}', [TaskController::class, 'greetOptional'])
    ->name('greet-optional');

// ── A route group with MIDDLEWARE (not just prefix/name) ────────────────
// throttle:api-demo references the named rate limiter defined in
// AppServiceProvider::boot() — every route inside this group shares that
// one rule, declared once instead of repeated per-route. Hit
// GET /api/limited/ping more than 30 times inside a minute and Laravel
// itself returns 429 Too Many Requests, with zero code written in
// PingController-equivalent logic to enforce it.
Route::middleware('throttle:api-demo')->prefix('limited')->name('limited.')->group(function () {
    // A closure route — the simplest form routing can take. Perfectly
    // valid for something this trivial; anything with real logic belongs
    // in a controller method instead, the way every other route here
    // works.
    Route::get('/ping', function () {
        return response()->json([
            'message' => 'pong',
            'note' => 'This route is rate-limited via the api-demo limiter '
                .'(30 requests/minute/IP) — refresh this 31+ times inside a '
                .'minute to see Laravel return 429 automatically.',
        ]);
    })->name('ping');
});

// ── Proof that named routes generate real, working URLs ─────────────────
Route::get('/route-links', [RouteLinksController::class, 'index'])
    ->name('route-links');

// ═══════════════════════════════════════════════════════════════════════
// Week 1, Day 6 — the request lifecycle, made observable.
// ═══════════════════════════════════════════════════════════════════════
Route::prefix('lifecycle')->name('lifecycle.')->group(function () {

    // Two route-specific middleware, stacked. Execution order (see
    // TraceMiddleware's class comment for the full "onion" explanation):
    //   RequestIdMiddleware (global, applied to the whole api group —
    //     runs before EITHER of these, on every api route, not just
    //     this one) → Outer (in) → Inner (in) → controller →
    //     Inner (out) → Outer (out)
    // "Outer" is listed FIRST, making it the outermost layer: first in,
    // last out. Reverse the two labels in this array and the reported
    // pipeline_trace order reverses to match.
    Route::get('/trace', [LifecycleController::class, 'trace'])
        ->middleware(['trace:Outer', 'trace:Inner'])
        ->name('trace');

    // No route-specific middleware listed here AT ALL — used to prove
    // RequestIdMiddleware still runs (check the X-Request-Id response
    // header), because it's global to the whole 'api' group, not
    // something any individual route opts into.
    Route::get('/plain', [LifecycleController::class, 'plain'])
        ->name('plain');
});
