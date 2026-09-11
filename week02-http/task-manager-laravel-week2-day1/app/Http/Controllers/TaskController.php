<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// ── A real resource controller, finally ──────────────────────────────────
// Week 1 Days 5–7 used a deliberately minimal TaskController stub. This
// is the actual convention Route::apiResource() expects:
// index/store/show/update/destroy, named and shaped to match RESTful
// semantics exactly. apiResource() deliberately OMITS create/edit —
// those exist purely to return HTML FORMS for creating/editing a
// resource, which has no meaning for a JSON API that never renders a
// form at all.
class TaskController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            array_map(fn (Task $task) => $task->toArray(), Task::all())
        );
    }

    // ── Route model binding in action ────────────────────────────────────
    // By the time this method runs, Laravel has ALREADY: taken the raw
    // route segment, resolved an empty Task via the CONTAINER (the exact
    // same automatic-resolution mechanism from Week 1 Days 1–2), called
    // ITS resolveRouteBinding() method, and either got back a real,
    // populated Task or triggered an automatic 404 if it returned null.
    // This method never runs at all for a task id that doesn't exist —
    // there's no manual "if not found" branch to write here.
    public function show(Task $task): JsonResponse
    {
        return response()->json($task->toArray());
    }

    public function store(Request $request): JsonResponse
    {
        // Real validated creation is Week 2 Day 2's topic (Form Requests).
        // Kept honest here rather than faking real persistence that
        // doesn't exist yet (Week 3, with Eloquent).
        return response()->json([
            'message' => 'Not implemented yet — real validation arrives '
                .'Week 2 Day 2, real persistence arrives Week 3.',
        ], 501);
    }

    public function update(Request $request, Task $task): JsonResponse
    {
        return response()->json([
            'message' => 'Not implemented yet — real validation arrives '
                .'Week 2 Day 2, real persistence arrives Week 3.',
        ], 501);
    }

    public function destroy(Task $task): JsonResponse
    {
        return response()->json([
            'message' => 'Not implemented yet — real persistence arrives Week 3.',
        ], 501);
    }
}
