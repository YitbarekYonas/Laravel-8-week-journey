<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

// ── Deliberately minimal — this is NOT this week's real controller work ──
// Hardcoded, in-memory data, no database, no validation, no service
// layer. Today's lesson is the ROUTING layer sitting in front of a
// controller — required parameters, constraints, optional parameters,
// named routes — not the controller conventions themselves (that's Week
// 2 Day 1) or real persistence (that's Week 3, with Eloquent). This class
// exists purely to give today's routes something realistic to point at.
class TaskController extends Controller
{
    private array $tasks = [
        1 => ['id' => 1, 'title' => 'Set up routing', 'status' => 'DONE'],
        2 => ['id' => 2, 'title' => 'Add route constraints', 'status' => 'IN_PROGRESS'],
        3 => ['id' => 3, 'title' => 'Learn named routes', 'status' => 'TODO'],
    ];

    public function index(): JsonResponse
    {
        return response()->json(array_values($this->tasks));
    }

    // The {task} route parameter arrives here ALREADY guaranteed to be
    // numeric — routes/api.php's ->whereNumber('task') constraint rejects
    // anything else (e.g. GET /api/tasks/abc) with a 404 before this
    // method is ever invoked. This method never has to defensively check
    // "is this actually a number" — the routing layer already did.
    public function show(int $task): JsonResponse
    {
        if (! isset($this->tasks[$task])) {
            return response()->json(['message' => "Task {$task} not found"], 404);
        }

        return response()->json($this->tasks[$task]);
    }

    // ── Optional route parameter ─────────────────────────────────────────
    // routes/api.php declares this as {name?} — the trailing ? makes the
    // segment optional. GET /api/greet-optional (no segment at all) and
    // GET /api/greet-optional/Sam BOTH match this same route; the
    // PHP-level default value below is what fills in when the segment is
    // absent.
    public function greetOptional(?string $name = 'friend'): JsonResponse
    {
        return response()->json([
            'message' => "Hello, {$name}!",
            'note' => '"name" is an OPTIONAL route parameter — '
                .'/api/greet-optional with no segment at all still matches '
                .'this route, falling back to the "friend" default declared '
                .'in this method\'s signature.',
        ]);
    }
}
