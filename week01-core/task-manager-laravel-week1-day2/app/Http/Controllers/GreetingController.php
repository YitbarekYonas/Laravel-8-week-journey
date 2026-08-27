<?php

namespace App\Http\Controllers;

use App\Services\Contracts\GreetingServiceInterface;
use Illuminate\Http\JsonResponse;

class GreetingController extends Controller
{
    // ── Automatic resolution — no manual container calls anywhere ──────
    // This constructor asks for a GreetingServiceInterface. It has NO
    // idea that FormalGreetingService is the concrete class actually
    // supplied — that decision lives entirely in AppServiceProvider.
    //
    // Nothing here calls app()->make() or App::bind() or anything
    // container-specific. When Laravel needs to instantiate this
    // controller to handle a request, it inspects the constructor's type
    // hints via reflection, sees `GreetingServiceInterface`, and asks the
    // container to resolve one automatically. This is "automatic
    // resolution" — the mechanism that makes dependency injection in
    // Laravel largely invisible once the binding is registered.
    public function __construct(
        private readonly GreetingServiceInterface $greetingService,
    ) {}

    public function greet(string $name): JsonResponse
    {
        return response()->json([
            'message' => $this->greetingService->greet($name),
            // Reveals which concrete class actually got resolved — purely
            // for this Day 1 demonstration, so hitting the route makes
            // the binding decision visible without reading source code.
            'resolved_implementation' => get_class($this->greetingService),
        ]);
    }
}
