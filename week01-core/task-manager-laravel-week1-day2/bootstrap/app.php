<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// ── The Laravel 11+ way of doing this ───────────────────────────────────
// In Laravel 10 and earlier, this project would have had app/Http/Kernel.php
// (HTTP middleware stack) and app/Console/Kernel.php (scheduled commands),
// plus app/Exceptions/Handler.php. Laravel 11 consolidated all three into
// THIS single file — one place to see the entire application's shape at a
// glance, instead of three separate files you had to already know existed.
//
// The request lifecycle concept from Week 1 Day 6 ("HTTP kernel, middleware
// pipeline") hasn't gone away — it's configured HERE now via the
// ->withMiddleware() call below, instead of by editing a Kernel.php
// property array.
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Global/route middleware groups get configured here — e.g.
        // $middleware->api(prepend: [...]) for API-wide middleware, or
        // $middleware->alias(['custom' => CustomMiddleware::class]) for
        // named middleware used in routes. Week 2 Day 4 covers writing
        // and registering custom middleware in depth.
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Global exception rendering configuration — the Laravel 11
        // equivalent of the old Handler.php's render() method. Week 6
        // Day 2 covers this in depth: custom exceptions, JSON error
        // shapes, the HTTP exception hierarchy.
    })->create();
