<?php

namespace App\Http\Controllers;

use App\Services\Contracts\IdGeneratorInterface;
use Illuminate\Http\JsonResponse;

class IdDemoController extends Controller
{
    // ── Manual resolution via app()->make() ─────────────────────────────
    // Every previous example (GreetingController) used AUTOMATIC
    // resolution — a constructor type-hint, resolved for us. That's the
    // common case, but it only resolves ONCE per request (when the
    // controller itself is instantiated). To actually observe whether the
    // container reuses an instance across MULTIPLE resolutions within the
    // same request, we have to ask the container directly, more than
    // once, inside the same method — which is exactly what app()->make()
    // is for: an explicit, on-demand resolution call.
    public function demonstrate(): JsonResponse
    {
        $first = app(IdGeneratorInterface::class)->next();
        $second = app(IdGeneratorInterface::class)->next();
        $third = app()->make(IdGeneratorInterface::class)->next();

        // app(Interface::class) and app()->make(Interface::class) are
        // exactly equivalent — app() with no arguments returns the
        // container itself; app('foo') is shorthand for
        // app()->make('foo'). Both calls above resolve the SAME binding.
        return response()->json([
            'resolutions' => [$first, $second, $third],
            'explanation' => 'Check AppServiceProvider — IdGeneratorInterface is '
                .'currently bound as a singleton(). All three "instance #" numbers '
                .'above should be IDENTICAL (same object, reused), and the counter '
                .'should genuinely increment: id-1, id-2, id-3. Switch the binding '
                .'to bind() instead and refresh — every "instance #" will differ, '
                .'and every call will show id-1, because a brand-new object is '
                .'created (and its counter reset to zero) on every single resolution.',
        ]);
    }
}
