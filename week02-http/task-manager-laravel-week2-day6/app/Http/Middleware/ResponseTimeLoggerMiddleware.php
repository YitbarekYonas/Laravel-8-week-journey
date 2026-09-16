<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

// ── Terminable middleware ────────────────────────────────────────────────
// A middleware class becomes "terminable" simply by having a terminate()
// method — no interface to implement, no special registration. Laravel
// detects it automatically and calls terminate() on THIS SAME instance
// after the response has ALREADY been sent to the client. This is the
// right tool for anything that should happen as a side effect of a
// request WITHOUT making the client wait for it — logging, cleanup,
// analytics — since terminate()'s work happens strictly after the
// response is already on its way out.
//
// Honest caveat: I'm confident in "same instance, called after the
// response is sent" as documented, long-stable Laravel behavior, but I
// don't have vendor/ in this sandbox to trace the exact Kernel/Pipeline
// code that guarantees it. Worth a quick look at
// vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php's
// terminate() method once you run composer install, if you want to see
// the mechanism directly.
class ResponseTimeLoggerMiddleware
{
    private float $startedAt;

    public function handle(Request $request, Closure $next): Response
    {
        // Recorded on THIS instance — terminate() below relies on being
        // called on the SAME object to read it back.
        $this->startedAt = microtime(true);

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        $durationMs = round((microtime(true) - $this->startedAt) * 1000, 2);

        // This log write happens AFTER the client already has their
        // response — it cannot add even a millisecond of perceived
        // latency to the request, which is the entire point of doing
        // this work in terminate() instead of at the end of handle().
        Log::info('Request completed', [
            'method' => $request->method(),
            'path' => $request->path(),
            'status' => $response->getStatusCode(),
            'duration_ms' => $durationMs,
        ]);
    }
}
