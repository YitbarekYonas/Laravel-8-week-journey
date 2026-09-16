<?php

namespace App\Http\Resources;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

// ── A ResourceCollection — many resources PLUS collection-level metadata ──
// TaskResource::collection($tasks) alone would give an anonymous
// collection wrapper with nowhere to attach extra top-level data.
// Extending ResourceCollection explicitly is what lets this class add a
// "status_summary" key alongside "data" — information describing the
// COLLECTION as a whole, not any single task in it (directly reusing the
// same status-counting idea as Week 2 Day 1's TaskSummaryController, now
// attached to the paginated list itself instead of a separate endpoint).
class TaskCollection extends ResourceCollection
{
    public $collects = TaskResource::class;

    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
        ];
    }

    // with() adds keys ALONGSIDE "data" and "links"/"meta" — Laravel
    // automatically adds "links" and "meta" itself when the underlying
    // resource is a paginator (see TaskController::index()), so
    // "status_summary" is deliberately a DIFFERENT top-level key,
    // avoiding any ambiguity about how it'd interact with Laravel's own
    // automatic pagination metadata.
    //
    // Recomputed directly from Task::all() here, rather than derived from
    // $this->collection — the exact internal shape of $this->collection
    // (raw items vs. already-wrapped TaskResource instances) isn't
    // something I can verify against vendor source in this sandbox, so
    // going straight back to the model sidesteps that uncertainty
    // entirely and is guaranteed correct either way.
    public function with(Request $request): array
    {
        $allTasks = Task::all();
        $statuses = collect($allTasks)->pluck('status');

        return [
            'status_summary' => [
                'total_across_all_pages' => count($allTasks),
                'by_status' => [
                    'TODO' => $statuses->filter(fn ($s) => $s === 'TODO')->count(),
                    'IN_PROGRESS' => $statuses->filter(fn ($s) => $s === 'IN_PROGRESS')->count(),
                    'DONE' => $statuses->filter(fn ($s) => $s === 'DONE')->count(),
                ],
            ],
        ];
    }
}
