<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// ── REAL custom middleware — genuine branching logic, not instrumentation ──
// Week 1 Day 6's TraceMiddleware/RequestIdMiddleware were deliberately
// minimal — pure observation of the pipeline, explicitly flagged at the
// time as narrower than what "real" middleware-authoring looks like. This
// is that real treatment: middleware that can actually REJECT a request
// before it ever reaches a controller, based on genuine application logic.
//
// ── An honest scope note ──────────────────────────────────────────────────
// This checks a single shared secret header against one configured
// value — a coarse GATE, not real user authentication. It can tell
// WHETHER a caller knows a shared secret, never WHO the caller is. Real
// per-user authentication (Laravel Sanctum, tokens tied to individual
// users, login/logout) is Week 5's topic — this is deliberately simpler,
// and shouldn't be mistaken for it.
class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $providedKey = $request->header('X-Api-Key');
        $expectedKey = config('security.api_key');

        if ($providedKey !== $expectedKey) {
            // Using Week 2 Day 3's error() macro here — the first place
            // outside TaskController it gets used, proving it's a
            // genuinely reusable, app-wide convention rather than
            // something tied to one controller.
            return response()->error(
                'Missing or invalid X-Api-Key header.',
                401
            );
        }

        return $next($request);
    }
}
