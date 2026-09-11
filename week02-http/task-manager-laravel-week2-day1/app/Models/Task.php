<?php

namespace App\Models;

use Illuminate\Contracts\Routing\UrlRoutable;

// ── A plain PHP object today — a real Eloquent model in Week 3 ──────────
// This class lives in app/Models/ (the conventional home for an Eloquent
// model) even though it isn't one yet. Week 3 will make it `extends
// Model`, back it with a real database table, and delete the hardcoded
// $records array below — but its NAME, NAMESPACE, and its role as "the
// thing route model binding resolves" won't change at all. Everything
// built today keeps working unmodified once that happens.
//
// ── Route model binding's REAL mechanism, without needing a database ────
// Route model binding isn't Eloquent-specific magic — it's built on the
// Illuminate\Contracts\Routing\UrlRoutable interface. Eloquent's own
// Model class implements this same interface; ANY class can. Implementing
// it here, on a plain in-memory class, is what lets
// TaskController::show(Task $task) resolve automatically today, with
// zero database involved — and it's the exact same mechanism that will
// still be running, unchanged, once Task becomes a real Eloquent model.
class Task implements UrlRoutable
{
    private static array $records = [
        1 => ['id' => 1, 'title' => 'Set up routing', 'status' => 'DONE'],
        2 => ['id' => 2, 'title' => 'Add route constraints', 'status' => 'IN_PROGRESS'],
        3 => ['id' => 3, 'title' => 'Learn named routes', 'status' => 'TODO'],
    ];

    public function __construct(
        public readonly ?int $id = null,
        public readonly ?string $title = null,
        public readonly ?string $status = null,
    ) {}

    public static function find(int $id): ?self
    {
        $record = self::$records[$id] ?? null;

        return $record ? new self(...$record) : null;
    }

    public static function all(): array
    {
        return array_values(array_map(
            fn (array $record) => new self(...$record),
            self::$records
        ));
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'status' => $this->status,
        ];
    }

    // ── Illuminate\Contracts\Routing\UrlRoutable ─────────────────────────
    // Honest caveat: I'm confident in this interface's shape from
    // documented Laravel behavior, but I don't have vendor/ in this
    // sandbox to verify the exact method signatures against source.
    // Worth a quick check once you run composer install, via:
    // vendor/laravel/framework/src/Illuminate/Contracts/Routing/UrlRoutable.php

    public function getRouteKey(): mixed
    {
        return $this->id;
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }

    public function resolveRouteBinding($value, $field = null): ?self
    {
        // THIS is the method Laravel's router actually calls. $value is
        // the raw route segment as a string (e.g. "3" from /api/tasks/3).
        // Returning null here is what makes Laravel automatically 404 —
        // no manual "if not found" branch needed in the controller at all.
        return static::find((int) $value);
    }

    public function resolveChildRouteBinding($childType, $value, $field): ?self
    {
        // Only relevant for NESTED resource routes (e.g.
        // /users/{user}/tasks/{task}) — not used by this project yet.
        return null;
    }
}
