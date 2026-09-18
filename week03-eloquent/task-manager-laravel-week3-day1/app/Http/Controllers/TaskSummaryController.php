<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\JsonResponse;

// ── A single-action controller ───────────────────────────────────────────
// Not every endpoint is CRUD. "Give me a summary of tasks by status"
// doesn't map to index/show/store/update/destroy at all — forcing it
// into one of those names would misrepresent what the action actually
// does. A single-action controller is Laravel's answer: one class, one
// job, invoked via PHP's __invoke() magic method instead of a named
// method — see routes/api.php, where this class is referenced directly,
// with no [Controller::class, 'method'] array at all.
class TaskSummaryController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $tasks = Task::all();

        $counts = [];
        foreach ($tasks as $task) {
            $counts[$task->status] = ($counts[$task->status] ?? 0) + 1;
        }

        return response()->json([
            'total' => count($tasks),
            'by_status' => $counts,
        ]);
    }
}
