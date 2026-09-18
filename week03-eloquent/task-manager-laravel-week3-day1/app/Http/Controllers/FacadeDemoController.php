<?php

namespace App\Http\Controllers;

use App\Facades\Greeting;
use Illuminate\Http\JsonResponse;

class FacadeDemoController extends Controller
{
    public function greet(string $name): JsonResponse
    {
        $message = Greeting::greet($name);
        $resolvedInstance = Greeting::getFacadeRoot();

        return response()->json([
            'message' => $message,
            'facade_resolved_to' => get_class($resolvedInstance),
            'explanation' => 'Greeting::greet() and Greeting::getFacadeRoot() '
                .'both trigger the exact same container resolution.',
            'compare_to' => [
                'note' => 'GreetingController gets FormalGreetingService via a '
                    .'CONTEXTUAL binding specific to that class (Week 1 Day 2). '
                    .'This facade call has no "consuming class" context at all, '
                    .'so it falls through to the GLOBAL default instead. As of '
                    .'the Week 1 Day 7 mini-project, that global default is '
                    .'picked by GreetingStrategyServiceProvider from '
                    .'config(\'greeting.active_strategy\'), currently "'
                    .config('greeting.active_strategy').'" via .env.',
            ],
        ]);
    }
}
