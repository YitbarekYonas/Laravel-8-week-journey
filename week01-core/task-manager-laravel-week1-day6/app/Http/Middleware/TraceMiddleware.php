<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// ── A deliberately minimal middleware — instrumentation, not business logic ──
//
// Writing REAL middleware (parameters used for actual authorization
// logic, terminable middleware's terminate() hook, authoring your own
// middleware GROUPS) is Week 2 Day 4's topic, not today's. This class
// exists for exactly one purpose: to make Day 6's actual subject — the
// middleware PIPELINE itself — externally observable. Without some
// concrete middleware to instrument with, "the pipeline" stays an
// abstract diagram instead of something you can watch happen.
//
// ── The "onion" model, in code ──────────────────────────────────────────
// Everything ABOVE the $next($request) call runs on the way IN — before
// the controller (or any middleware further inside this one) has run at
// all. Everything BELOW that call only runs on the way BACK OUT, after
// the entire inner chain — every middleware nested inside this one, then
// the controller itself — has already finished and produced a response.
//
// With two of these stacked (see routes/api.php: ['trace:Outer',
// 'trace:Inner']), the execution order is NOT top-to-bottom twice — it's:
//   Outer (in) → Inner (in) → controller → Inner (out) → Outer (out)
// The first-listed middleware is the OUTERMOST layer: first in, last out.
class TraceMiddleware
{
    public function handle(Request $request, Closure $next, string $label): Response
    {
        $this->appendTrace($request, "{$label} → entering (before controller)");

        // This is the actual pipeline mechanism. $next is a closure that,
        // when called, invokes whatever's NEXT in the chain — either
        // another middleware, or (if this is the innermost one) the
        // controller itself. The return value is the RESPONSE that
        // eventually came back from the controller, having already
        // passed back out through every middleware nested inside this
        // one.
        $response = $next($request);

        $this->appendTrace($request, "{$label} → exiting (after controller, before response sent)");

        // ── Middleware can rewrite the response on the way out ─────────
        // The controller already built and returned its JsonResponse
        // BEFORE this line runs — $next() only returns once that entire
        // inner chain is done. Modifying $response here, after the fact,
        // is exactly how middleware like Laravel's own CORS or session
        // middleware add headers/cookies to a response the controller
        // never knew anything about.
        if ($response instanceof JsonResponse) {
            $data = $response->getData(true);
            $data['pipeline_trace'] = $request->attributes->get('__trace', []);
            $response->setData($data);
        }

        return $response;
    }

    private function appendTrace(Request $request, string $entry): void
    {
        // Request attributes are the standard place to pass data BETWEEN
        // middleware and the eventual controller — they're attached to
        // the request object itself, so anything further along the same
        // pipeline (more inner middleware, the controller) can read what
        // earlier layers left behind.
        $trace = $request->attributes->get('__trace', []);
        $trace[] = $entry;
        $request->attributes->set('__trace', $trace);
    }
}
