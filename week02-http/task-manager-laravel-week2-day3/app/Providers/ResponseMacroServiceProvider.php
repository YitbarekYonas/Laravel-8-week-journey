<?php

namespace App\Providers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

// ── Response macros — extending the response factory, not the container ──
// Registering macros doesn't need anything from the container beyond the
// Response factory itself, so — unlike Week 1 Day 3's boot()-only rule
// for resolving ANOTHER provider's binding — putting this in register()
// would technically also work here; nothing here depends on any other
// provider having registered first. It's placed in boot() anyway because
// that's the established Laravel convention for this KIND of setup
// (macros, view composers): keeping every provider's register() focused
// purely on binding is a habit worth keeping even in the one case that
// wouldn't strictly require it.
class ResponseMacroServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // ── A standardized success envelope ──────────────────────────────
        // Response::macro() adds a NEW method to Laravel's response
        // factory, callable anywhere as response()->success(...) — exactly
        // like the BUILT-IN response()->json(...) this project has used
        // since Day 1. Once this runs at boot, there's no functional
        // distinction between a macro and a method Laravel shipped with —
        // both are just methods on the same factory object from that
        // point on.
        Response::macro('success', function (mixed $data = null, string $message = 'OK', int $status = 200): JsonResponse {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $data,
            ], $status);
        });

        // ── A standardized error envelope ────────────────────────────────
        // A deliberately LIGHTWEIGHT preview of Week 6 Day 2's real topic
        // (a proper global exception handler, with a genuinely consistent
        // error shape enforced app-wide). This macro only standardizes
        // the shape for the ONE place that calls it directly
        // (TaskController::markAsDone) — Laravel's own automatic 404s
        // (route model binding) and 422s (Form Request validation) still
        // use Laravel's own default shapes, completely untouched by this.
        // Unifying every error response in the app into one shape is
        // explicitly Week 6's job, not today's.
        Response::macro('error', function (string $message, int $status = 400, array $errors = []): JsonResponse {
            return response()->json([
                'success' => false,
                'message' => $message,
                'errors' => $errors,
            ], $status);
        });
    }
}
