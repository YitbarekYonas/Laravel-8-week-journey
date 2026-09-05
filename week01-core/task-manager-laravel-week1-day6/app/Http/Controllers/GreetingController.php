<?php

namespace App\Http\Controllers;

use App\Services\Contracts\GreetingServiceInterface;
use Illuminate\Http\JsonResponse;

class GreetingController extends Controller
{
    public function __construct(
        private readonly GreetingServiceInterface $greetingService,
    ) {}

    public function greet(string $name): JsonResponse
    {
        return response()->json([
            'message' => $this->greetingService->greet($name),
            'resolved_implementation' => get_class($this->greetingService),
        ]);
    }
}
