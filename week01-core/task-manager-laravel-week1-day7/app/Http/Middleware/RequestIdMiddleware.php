<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// ── GLOBAL middleware — the outermost layer of the entire pipeline ──────
// TraceMiddleware (see that class) only runs on the ONE route that
// explicitly lists it. This middleware is registered differently — in
// bootstrap/app.php's withMiddleware() closure, applied to the whole
// 'api' middleware group — so it runs on EVERY API request in this app,
// whether or not that route mentions it at all. It's the true outermost
// layer: it wraps even TraceMiddleware's own "Outer"/"Inner" pair on the
// one route that has them.
//
// A request ID is a genuinely common real-world use for global
// middleware: tag every request with a unique identifier, early, so it
// can be included in every log line and error report generated while
// handling that request — without every single controller needing to
// know or care that this exists.
class RequestIdMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = (string) \Illuminate\Support\Str::uuid();

        // Attached to the request so anything downstream — controllers,
        // other middleware, exception handlers — can read it via
        // $request->attributes->get('request_id') without needing it
        // passed through every function signature explicitly.
        $request->attributes->set('request_id', $requestId);

        $response = $next($request);

        // Added to the OUTGOING response as a header — the client
        // receives this on every single API response from this app,
        // useful for correlating a support request ("what's in
        // X-Request-Id on your failed request?") back to server-side
        // logs for that exact request.
        $response->headers->set('X-Request-Id', $requestId);

        return $response;
    }
}
