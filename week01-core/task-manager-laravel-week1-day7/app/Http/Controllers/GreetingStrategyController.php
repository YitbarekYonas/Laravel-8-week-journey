<?php

namespace App\Http\Controllers;

use App\Services\Contracts\GreetingServiceInterface;
use Illuminate\Http\JsonResponse;

// ── Week 1 Day 7 Mini-Project — everything this week built, in one place ──
// This single endpoint genuinely touches every day of Week 1:
//   Day 1–2: automatic constructor resolution of GreetingServiceInterface
//   Day 3:   the resolved class comes from a dedicated SERVICE PROVIDER
//            (GreetingStrategyServiceProvider), not a bare bind() here
//   Day 4:   WHICH class gets resolved is driven by config('greeting.
//            active_strategy'), itself sourced from .env
//   Day 5:   reached via a properly named, grouped route
//   Day 6:   routed through TraceMiddleware — check the route definition
//            in routes/api.php and the pipeline_trace in this response
class GreetingStrategyController extends Controller
{
    public function __construct(
        private readonly GreetingServiceInterface $greetingService,
    ) {}

    public function greet(string $name): JsonResponse
    {
        $message = $this->greetingService->greet($name);

        // ── Environment-based behavior — the mini-project's other half ──
        // Mirrors the Spring Boot journey's "profile-based default
        // message (dev vs prod)" exercise: the SAME config-driven
        // strategy behaves identically everywhere, but this controller
        // layers one additional, environment-aware behavior on top,
        // reading config('app.env') directly (Week 1 Day 4's config()
        // helper, never env() — this is application code, not a config
        // file).
        if (config('app.env') !== 'production') {
            $message .= ' [Running in '.config('app.env').' — this suffix '
                .'is suppressed in production]';
        }

        return response()->json([
            'message' => $message,
            'active_strategy' => config('greeting.active_strategy'),
            'resolved_implementation' => get_class($this->greetingService),
            'environment' => config('app.env'),
        ]);
    }
}
