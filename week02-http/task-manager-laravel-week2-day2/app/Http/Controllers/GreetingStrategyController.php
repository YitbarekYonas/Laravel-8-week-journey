<?php

namespace App\Http\Controllers;

use App\Services\Contracts\GreetingServiceInterface;
use Illuminate\Http\JsonResponse;

class GreetingStrategyController extends Controller
{
    public function __construct(
        private readonly GreetingServiceInterface $greetingService,
    ) {}

    public function greet(string $name): JsonResponse
    {
        $message = $this->greetingService->greet($name);

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
