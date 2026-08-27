<?php

namespace App\Http\Controllers;

use App\Services\Contracts\GreetingServiceInterface;
use Illuminate\Http\JsonResponse;

// ── Contextual binding — the payoff ─────────────────────────────────────
// This controller's constructor looks IDENTICAL to GreetingController's —
// same type-hint, same GreetingServiceInterface dependency. A single
// global bind() (Day 1's approach) can only ever satisfy this contract
// one way for the WHOLE app: every controller that asks gets the same
// concrete class.
//
// But here, THIS controller should get the casual tone, while
// GreetingController keeps the formal one — a genuine, real-world need
// (imagine a public-facing endpoint vs an internal admin one). See
// AppServiceProvider::register() for the contextual binding that makes
// this controller-specific resolution possible, using the exact same
// interface type-hint as GreetingController.
class CasualGreetingController extends Controller
{
    public function __construct(
        private readonly GreetingServiceInterface $greetingService,
    ) {}

    public function greet(string $name): JsonResponse
    {
        return response()->json([
            'message' => $this->greetingService->greet($name),
            'resolved_implementation' => get_class($this->greetingService),
            'note' => 'Same interface as GreetingController, different concrete '
                .'class — resolved via contextual binding based on which '
                .'controller is asking.',
        ]);
    }
}
