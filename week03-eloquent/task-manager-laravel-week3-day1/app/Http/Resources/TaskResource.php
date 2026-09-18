<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// ── A JsonResource — the centralized replacement for Task::toArray() ────
// Every controller action returning a single task now goes through THIS
// class instead of calling $task->toArray() directly. The payoff: ONE
// place decides the public JSON shape of a task, regardless of how many
// different controller methods return one — Week 2 Day 1 through Day 4's
// scattered ->toArray() calls all converge here today.
//
// Inside a resource, $this-> forwards to the underlying wrapped object
// (a Task instance here) via JsonResource's own magic __get — so
// $this->title reads Task::$title, $this->attachmentPath reads
// Task::$attachmentPath, and so on, with no explicit property mapping
// needed for the simple cases.
class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'status' => $this->status,

            // ── Conditional attribute — when() ────────────────────────────
            // Included ONLY if the condition is true; otherwise the key is
            // OMITTED from the response entirely — not present as null.
            // A task with no attachment simply has no "attachment_path"
            // key at all, which is a meaningfully cleaner contract than
            // always including it as null.
            'attachment_path' => $this->when(
                $this->attachmentPath !== null,
                $this->attachmentPath,
            ),

            // ── Conditional attribute — mergeWhen() ───────────────────────
            // Merges SEVERAL keys into the response at once, conditionally,
            // as a group — the plural counterpart to when()'s single key.
            // Driven by a query parameter here (?include_meta=1), showing
            // conditional attributes can respond to the REQUEST, not just
            // the resource's own data.
            $this->mergeWhen($request->boolean('include_meta'), [
                'created_via' => 'API',
                'api_version' => 'v1',
            ]),

            'debug' => $this->when(
                $request->boolean('debug'),
                fn () => [
                    'resolved_at' => now()->toISOString(),
                    'resource_class' => static::class,
                ],
            ),
        ];
    }
}
