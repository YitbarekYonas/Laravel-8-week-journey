<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

// ── Named routes — the point isn't the name, it's what it BUYS you ──────
// Every URL in the response below is generated via the route() helper,
// from a route NAME plus parameters — never hardcoded as a literal
// string like '/api/tasks/3'. This is the entire value proposition of
// naming routes: rename a URI in routes/api.php (e.g. /tasks becomes
// /task-items) and every route()-generated link across the WHOLE app
// updates automatically. A hardcoded string URL would silently go stale
// the moment the underlying URI changed, with no error anywhere to catch
// it.
class RouteLinksController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'note' => 'Every link below came from route(\'name\', [...params]), '
                .'never a hardcoded URL string. Try renaming a URI in '
                .'routes/api.php (keep the ->name() the same) — every link '
                .'here keeps working with zero changes to this controller.',
            'links' => [
                'tasks_index' => route('tasks.index'),
                'tasks_show_example' => route('tasks.show', ['task' => 3]),
                'greet_optional_with_name' => route('greet-optional', ['name' => 'Sam']),
                'greet_optional_without_name' => route('greet-optional'),
                'demo_greet' => route('demo.greet', ['name' => 'Sam']),
                'demo_config' => route('demo.config'),
                'limited_ping' => route('limited.ping'),
                'lifecycle_trace' => route('lifecycle.trace'),
                'lifecycle_plain' => route('lifecycle.plain'),
                'mini_project_greet' => route('mini-project.greet', ['name' => 'Sam']),
            ],
        ]);
    }
}
