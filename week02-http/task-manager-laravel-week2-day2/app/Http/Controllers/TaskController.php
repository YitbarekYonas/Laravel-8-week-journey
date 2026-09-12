<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use Illuminate\Http\JsonResponse;

class TaskController extends Controller
{
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
}
