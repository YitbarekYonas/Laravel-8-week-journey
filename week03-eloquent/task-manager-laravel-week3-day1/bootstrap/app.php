<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Week 1 Day 6 — global-to-the-api-group middleware.
        $middleware->api(prepend: [
            \App\Http\Middleware\RequestIdMiddleware::class,
        ]);

        $middleware->alias([
            'trace' => \App\Http\Middleware\TraceMiddleware::class,
        ]);

        // ── Week 2 Day 4 — a CUSTOM middleware group ─────────────────────
        // Week 1's api()/web() calls above only ever MODIFIED Laravel's
        // own BUILT-IN groups. ->group() here defines a genuinely NEW,
        // named group from scratch — 'secure-api' doesn't exist anywhere
        // in Laravel itself; it's this project's own bundle. Any route
        // that lists ->middleware('secure-api') gets BOTH middleware
        // below applied together, in this exact order, without needing to
        // list either one individually. This is the same principle
        // behind Laravel's own 'web' and 'api' groups — just applied to
        // your own bundle instead of the framework's.
        //
        // Honest caveat: I'm confident ->group() is the method for this
        // based on documented Laravel 11 behavior, but I don't have
        // vendor/ in this sandbox to verify its exact signature against
        // source. Worth a quick check against
        // vendor/laravel/framework/src/Illuminate/Foundation/Configuration/Middleware.php
        // once you run composer install — if the method name has shifted,
        // that file is the authoritative source.
        $middleware->group('secure-api', [
            \App\Http\Middleware\ApiKeyMiddleware::class,
            \App\Http\Middleware\ResponseTimeLoggerMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
