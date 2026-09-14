<?php

namespace App\Http\Controllers;

use App\Services\Contracts\GreetingServiceInterface;
use Illuminate\Http\JsonResponse;

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
