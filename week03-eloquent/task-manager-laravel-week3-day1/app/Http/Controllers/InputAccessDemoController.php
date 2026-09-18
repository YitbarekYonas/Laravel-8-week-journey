<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// ── Accessing input — every common way, side by side ─────────────────────
// Deliberately NOT tied to any Form Request or validation at all — this
// endpoint accepts any POST body whatsoever and shows the different ways
// to pull data out of a plain Request object, each suited to a different
// situation. Compare this against TaskController::store(), which uses
// ->validated() specifically because it needs to TRUST its input; the
// methods here make no such guarantee — they return exactly what the
// client sent, whether or not it's well-formed.
class InputAccessDemoController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'all' => $request->all(),
            // everything the client sent, including fields no rule ever
            // asked for — the opposite of ->validated()'s trusted subset.

            'only_title_status' => $request->only(['title', 'status']),
            // just these two keys, whether or not they were actually
            // present in the request — missing keys are simply absent
            // from the returned array, not filled with null.

            'except_title' => $request->except(['title']),
            // everything EXCEPT this key — the inverse of only().

            'single_with_default' => $request->input('priority', 'MEDIUM'),
            // one specific key, with a fallback value used if the key is
            // entirely absent from the request.

            'has_title' => $request->has('title'),
            // true if the key is PRESENT at all — even if its value is an
            // empty string. Presence, not "meaningfully filled in."

            'filled_title' => $request->filled('title'),
            // true only if the key is present AND non-empty — the
            // distinction from has() matters for a field like
            // title="" (present, but not meaningfully filled).
        ]);
    }
}
