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
        1 => ['id' => 1, 'title' => 'Set up routing', 'status' => 'DONE', 'attachment_path' => null],
        2 => ['id' => 2, 'title' => 'Add route constraints', 'status' => 'IN_PROGRESS', 'attachment_path' => null],
        3 => ['id' => 3, 'title' => 'Learn named routes', 'status' => 'TODO', 'attachment_path' => null],
    ];

    public function __construct(
        public readonly ?int $id = null,
        public readonly ?string $title = null,
        public readonly ?string $status = null,
        public readonly ?string $attachmentPath = null,
    ) {}

    public static function find(int $id): ?self
    {
        $record = self::$records[$id] ?? null;

        return $record ? self::fromRecord($record) : null;
    }

    public static function all(): array
    {
        return array_values(array_map(
            fn (array $record) => self::fromRecord($record),
            self::$records
        ));
    }

    // ── Week 2 Day 2 — real (if honestly limited) mutation ────────────────
    // See this project's README for the important caveat: unlike a
    // long-running Spring Boot/JVM process, PHP's request lifecycle
    // re-executes this entire script from scratch on every single HTTP
    // request under the standard php-fpm / `php artisan serve` model.
    // That means $records resets to its three hardcoded entries above
    // EVERY request — a task created via create() below is fully real and
    // correctly validated WITHIN that one request/response cycle, but a
    // separate, later GET request will NOT see it. Real persistence
    // across requests needs an actual datastore — arriving Week 3 with
    // Eloquent + a real database.
    public static function create(array $attributes): self
    {
        $nextId = empty(self::$records) ? 1 : max(array_keys(self::$records)) + 1;

        $record = [
            'id' => $nextId,
            'title' => $attributes['title'],
            'status' => $attributes['status'],
            'attachment_path' => $attributes['attachment_path'] ?? null,
        ];

        self::$records[$nextId] = $record;

        return self::fromRecord($record);
    }

    public static function updateRecord(int $id, array $attributes): ?self
    {
        if (! isset(self::$records[$id])) {
            return null;
        }

        self::$records[$id] = array_merge(self::$records[$id], $attributes);

        return self::fromRecord(self::$records[$id]);
    }

    public static function deleteRecord(int $id): bool
    {
        if (! isset(self::$records[$id])) {
            return false;
        }

        unset(self::$records[$id]);

        return true;
    }

    private static function fromRecord(array $record): self
    {
        return new self(
            id: $record['id'] ?? null,
            title: $record['title'] ?? null,
            status: $record['status'] ?? null,
            attachmentPath: $record['attachment_path'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'status' => $this->status,
            'attachment_path' => $this->attachmentPath,
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
