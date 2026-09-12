<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

// Relocated here from TaskController (Week 1 Day 5) now that
// TaskController is a proper resource controller — "greet someone
// optionally" was never a resource action and never belonged there. A
// second, real-world-motivated example of a single-action controller:
// one job, no CRUD naming that fits it.
class GreetOptionalController extends Controller
{
    public function __invoke(?string $name = 'friend'): JsonResponse
    {
        return response()->json([
            'message' => "Hello, {$name}!",
            'note' => '"name" is an OPTIONAL route parameter (Week 1 Day 5) '
                .'— /api/greet-optional with no segment at all still '
                .'matches this route.',
        ]);
    }
}
