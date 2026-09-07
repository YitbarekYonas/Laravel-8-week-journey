<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Laravel 11+ consolidated app/Http/Kernel.php, app/Console/Kernel.php,
// and app/Exceptions/Handler.php into this single file. Provider
// registration itself lives in bootstrap/providers.php.
//
// ── The HTTP kernel didn't disappear — only the per-app file did ────────
// The KERNEL CLASS (Illuminate\Foundation\Http\Kernel) still exists
// inside the framework and still does exactly the same job it always
// did: run the request through the middleware pipeline, then dispatch it
// to the matched route. What Laravel 11 removed is the requirement that
// YOUR app keep its own app/Http/Kernel.php file just to configure that
// pipeline's contents. The withMiddleware() closure below is what
// configures the SAME underlying kernel now — just through a fluent
// method call instead of editing a $middleware property array on a class
// you had to already know existed.
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // ── Global-to-a-group middleware ─────────────────────────────────
        // ->api(prepend: [...]) adds RequestIdMiddleware to the FRONT of
        // the 'api' middleware group — meaning it runs on EVERY route
        // this app defines in routes/api.php, unconditionally, before any
        // route-specific middleware even starts. This is the outermost
        // layer of the entire pipeline for this app. (There's a
        // corresponding ->web(...) for the web middleware group, and a
        // bare ->append()/->prepend() for middleware that should apply to
        // literally every request regardless of group — not needed here,
        // since this project is API-only.)
        $middleware->api(prepend: [
            \App\Http\Middleware\RequestIdMiddleware::class,
        ]);

        // ── Named middleware alias ───────────────────────────────────────
        // Registers the short name 'trace' so routes/api.php can write
        // ->middleware('trace:Outer') instead of the fully-qualified
        // class name. This is purely a routing-layer convenience — it
        // doesn't change what the middleware does or when it runs.
        $middleware->alias([
            'trace' => \App\Http\Middleware\TraceMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
