<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskCollection;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

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
    // 409 Conflict — markAsDone() below.

    // ── Week 2 Day 5 — manual pagination, honestly ───────────────────────
    // A real Eloquent Builder's ->paginate() (Week 3 onward) queries the
    // database with LIMIT/OFFSET and constructs a LengthAwarePaginator
    // FOR you. Task isn't Eloquent yet, so there's no query to run — but
    // the RESPONSE SHAPE (links, meta: current_page/last_page/per_page/
    // total) doesn't actually come from the database query itself; it
    // comes from this exact LengthAwarePaginator class. Constructing it
    // manually here, from a plain in-memory slice, is what reveals that:
    // ->paginate() is convenient sugar around building this same object,
    // not a separate mechanism. Deeper pagination methods
    // (simplePaginate, cursorPaginate) and when to choose each are
    // explicitly Week 4 Day 2's topic — today only covers the shape one
    // default paginated response takes.
    public function index(Request $request): TaskCollection
    {
        $allTasks = Task::all();

        $perPage = max(1, (int) $request->query('per_page', 2));
        $currentPage = max(1, (int) $request->query('page', 1));

        $itemsForThisPage = collect($allTasks)
            ->slice(($currentPage - 1) * $perPage, $perPage)
            ->values();

        $paginator = new LengthAwarePaginator(
            $itemsForThisPage,
            count($allTasks),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()],
        );

        // Laravel's ResourceCollection detects that its underlying
        // resource is a paginator and automatically adds "links" and
        // "meta" (current_page, last_page, per_page, total) to the
        // response — on top of TaskCollection's own "data" and
        // "status_summary" keys.
        return new TaskCollection($paginator);
    }

    public function show(Task $task): TaskResource
    {
        return new TaskResource($task);
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('task-attachments');
        }

        $task = Task::create([
            ...$validated,
            'attachment_path' => $attachmentPath,
        ]);

        // ->response() converts the resource into a real JsonResponse —
        // only at that point can status code and headers be customized
        // further, since a bare JsonResource/ResourceCollection returned
        // directly from a controller doesn't expose those directly.
        return (new TaskResource($task))
            ->response()
            ->setStatusCode(201)
            ->header('Location', route('tasks.show', ['task' => $task->id]));
    }

    public function update(UpdateTaskRequest $request, Task $task): TaskResource
    {
        $validated = $request->validated();

        if ($request->hasFile('attachment')) {
            $validated['attachment_path'] = $request->file('attachment')->store('task-attachments');
        }

        $updated = Task::updateRecord($task->id, $validated);

        return new TaskResource($updated);
    }

    public function destroy(Task $task): JsonResponse
    {
        Task::deleteRecord($task->id);

        return response()->json(null, 204);
    }

    // ── Week 2 Day 3's macro, now paired with Week 2 Day 5's resource ───
    // TaskResource's transformation logic (conditional attributes and
    // all) is reused HERE too, nested inside the success() envelope's
    // "data" key — proving the resource is a genuinely reusable
    // transformation, not something tied to only the plain CRUD actions.
    public function markAsDone(Request $request, Task $task): JsonResponse
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

        return response()->success(
            (new TaskResource($updated))->toArray($request),
            'Task marked as done.'
        );
    }
}
