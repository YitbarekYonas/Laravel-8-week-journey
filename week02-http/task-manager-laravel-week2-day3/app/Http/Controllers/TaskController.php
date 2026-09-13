<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use Illuminate\Http\JsonResponse;

class TaskController extends Controller
{
    // ── HTTP status codes used across this controller, reviewed ─────────
    // 200 OK       — index(), show(), update() — a successful request with
    //                a response body the client actually needs.
    // 201 Created  — store() — a NEW resource was created; paired with a
    //                Location header pointing at it, per REST convention.
    // 204 No Content — destroy() — successful, but nothing to return.
    // 404 Not Found — AUTOMATIC, from route model binding (Week 2 Day 1) —
    //                never written explicitly anywhere in this class.
    // 422 Unprocessable Entity — AUTOMATIC, from Form Request validation
    //                (Week 2 Day 2) — also never written explicitly here.
    // 409 Conflict — markAsDone() below — new today, see that method.

    public function index(): JsonResponse
    {
        return response()->json(
            array_map(fn (Task $task) => $task->toArray(), Task::all())
        );
    }

    public function show(Task $task): JsonResponse
    {
        return response()->json($task->toArray());
    }

    // ── Week 2 Day 2 — real, validated creation ──────────────────────────
    // The StoreTaskRequest type-hint IS the validation step — by the time
    // this method body runs, every rule in StoreTaskRequest::rules() has
    // already passed. A malformed request never reaches this line at all;
    // Laravel returns 422 automatically before this method is even called.
    public function store(StoreTaskRequest $request): JsonResponse
    {
        // ->validated() returns ONLY the fields that were actually
        // declared in rules() — not the full, untrusted request body.
        // Contrast this with $request->all(), which would include
        // anything a client sent, validated or not (see
        // InputAccessDemoController for that distinction made explicit).
        $validated = $request->validated();

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            // ->store() saves the file to the 'local' disk (see
            // config/filesystems.php) under a generated, collision-safe
            // filename, and returns the relative path actually used —
            // never trust or reuse the client's original filename
            // directly for the path on disk.
            $attachmentPath = $request->file('attachment')->store('task-attachments');
        }

        $task = Task::create([
            ...$validated,
            'attachment_path' => $attachmentPath,
        ]);

        // 201 Created + Location header pointing at the new resource —
        // standard REST convention for a successful creation.
        return response()->json($task->toArray(), 201)
            ->header('Location', route('tasks.show', ['task' => $task->id]));
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('attachment')) {
            $validated['attachment_path'] = $request->file('attachment')->store('task-attachments');
        }

        $updated = Task::updateRecord($task->id, $validated);

        return response()->json($updated->toArray());
    }

    public function destroy(Task $task): JsonResponse
    {
        Task::deleteRecord($task->id);

        return response()->json(null, 204);
    }

    // ── Week 2 Day 3 — a genuine reason for 409 Conflict ─────────────────
    // A task can only reach DONE by first passing through IN_PROGRESS —
    // not straight from TODO, and not from DONE back to DONE. This is
    // NOT a validation failure (422) — the request itself is perfectly
    // well-formed, there's no malformed field anywhere. It's also not a
    // "not found" (404) — the task genuinely exists. 409 Conflict is the
    // status code that means exactly this: the request is well-formed and
    // the resource exists, but the action conflicts with the resource's
    // CURRENT STATE. Also using the new response()->success()/->error()
    // macros here, registered in ResponseMacroServiceProvider — this is
    // the first place in the project either macro is actually used.
    public function markAsDone(Task $task): JsonResponse
    {
        if ($task->status === 'DONE') {
            return response()->error("Task {$task->id} is already DONE.", 409);
        }

        if ($task->status === 'TODO') {
            return response()->error(
                "Task {$task->id} must be IN_PROGRESS before it can be marked DONE.",
                409
            );
        }

        $updated = Task::updateRecord($task->id, ['status' => 'DONE']);

        return response()->success($updated->toArray(), 'Task marked as done.');
    }
}
