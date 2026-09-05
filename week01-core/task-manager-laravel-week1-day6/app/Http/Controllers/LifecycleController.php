<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LifecycleController extends Controller
{
    // ── Where the controller sits in the pipeline ────────────────────────
    // By the time THIS method runs, the request has already passed
    // through: RequestIdMiddleware (global, applied to the whole 'api'
    // group) → TraceMiddleware:Outer → TraceMiddleware:Inner → Laravel's
    // own routing resolution (matching the URI to this exact method).
    // Everything this method does happens in the very CENTER of the
    // pipeline "onion" — the innermost layer, with every middleware still
    // waiting on the outside for this method to return before any of
    // them can finish running their own "exiting" code.
    public function trace(Request $request): JsonResponse
    {
        $trace = $request->attributes->get('__trace', []);
        $trace[] = 'LifecycleController@trace → controller logic running now (innermost point of the pipeline)';
        $request->attributes->set('__trace', $trace);

        return response()->json([
            'message' => 'This response gets REWRITTEN by TraceMiddleware on '
                .'the way back out — the "pipeline_trace" key below is not '
                .'the final version; check the actual HTTP response you '
                .'received for the complete, correct order.',
            'pipeline_trace' => $trace,
            'request_id_seen_by_controller' => $request->attributes->get('request_id'),
            'note' => 'request_id was set by RequestIdMiddleware — GLOBAL '
                .'middleware, applied to the whole api group in '
                .'bootstrap/app.php, not mentioned anywhere in this route\'s '
                .'definition. It reached this controller anyway, because '
                .'global middleware runs on every matching request '
                .'unconditionally.',
        ]);
    }

    // A second, plain route with NO route-specific middleware at all —
    // proof that RequestIdMiddleware (global) still runs on it, while
    // TraceMiddleware (route-specific, only listed on /lifecycle/trace)
    // does not. Check the X-Request-Id RESPONSE HEADER on this endpoint —
    // it's present here too, even though nothing in this route's
    // definition mentions RequestIdMiddleware.
    public function plain(): JsonResponse
    {
        return response()->json([
            'message' => 'This route has ZERO route-specific middleware '
                .'listed. Check the X-Request-Id response header anyway — '
                .'RequestIdMiddleware still ran, because it\'s global to the '
                .'whole api middleware group, not something this route opted '
                .'into individually.',
        ]);
    }
}
