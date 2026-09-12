<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LifecycleController extends Controller
{
    public function trace(Request $request): JsonResponse
    {
        $trace = $request->attributes->get('__trace', []);
        $trace[] = 'LifecycleController@trace → controller logic running now (innermost point of the pipeline)';
        $request->attributes->set('__trace', $trace);

        return response()->json([
            'message' => 'This response gets REWRITTEN by TraceMiddleware on '
                .'the way back out — check the actual HTTP response you '
                .'received for the complete, correct order.',
            'pipeline_trace' => $trace,
            'request_id_seen_by_controller' => $request->attributes->get('request_id'),
        ]);
    }

    public function plain(): JsonResponse
    {
        return response()->json([
            'message' => 'This route has ZERO route-specific middleware '
                .'listed. Check the X-Request-Id response header anyway — '
                .'RequestIdMiddleware is global to the whole api group.',
        ]);
    }
}
