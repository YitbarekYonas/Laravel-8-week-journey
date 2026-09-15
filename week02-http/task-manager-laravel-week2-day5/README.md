# Laravel Roadmap — Week 2, Day 5

[![Status](https://img.shields.io/badge/Status-Completed-brightgreen.svg)]()
[![Day](https://img.shields.io/badge/Day-5-blue.svg)]()
[![Topic](https://img.shields.io/badge/Topic-API%20Resources-orange.svg)]()

> **"->paginate() isn't a different mechanism from constructing a LengthAwarePaginator by hand. It's the same object — Eloquent just builds it for you."**

---

## ⚠️ Same Setup Note as Week 1–2

Hand-built Laravel 11 skeleton, no `vendor/` directory (no Packagist access in this sandbox). Run this first:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

---

## 🎯 Learning Objectives

- ✅ Replace scattered `->toArray()` calls with one centralized `JsonResource`
- ✅ Build a real `ResourceCollection` with genuine collection-level metadata, not just a list of items
- ✅ Use conditional attributes — `when()` for a single key, `mergeWhen()` for several at once
- ✅ Understand pagination's actual response shape by constructing it manually, without an Eloquent query behind it

---

## 💡 What I Learned Today

### 1. One Class Decides a Task's Public Shape

Every controller action returning a single task now goes through `TaskResource` instead of calling `$task->toArray()` directly — Week 2 Day 1 through Day 4's scattered calls all converge here:

```php
class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'status' => $this->status,
            // ...
        ];
    }
}
```

`$this->` inside a resource forwards to the underlying wrapped object via `JsonResource`'s own magic `__get` — `$this->title` reads `Task::$title` with no explicit mapping needed for the simple cases. The real payoff: change what a task looks like in JSON in exactly one place, and every controller action that returns one picks up the change automatically.

### 2. Conditional Attributes — `when()` and `mergeWhen()`

```php
'attachment_path' => $this->when($this->attachmentPath !== null, $this->attachmentPath),
```
Included **only** if the condition is true — otherwise the key is **omitted entirely**, not present as `null`. A task with no attachment genuinely has no `attachment_path` key at all.

```php
$this->mergeWhen($request->boolean('include_meta'), [
    'created_via' => 'API',
    'api_version' => 'v1',
]),
```
The plural counterpart — merges *several* keys into the response at once, as a group, conditionally. Both examples respond to the incoming `$request` directly, showing conditional attributes aren't limited to the resource's own data.

### 3. A Real `ResourceCollection`, Not Just `TaskResource::collection()`

`TaskResource::collection($tasks)` alone gives an anonymous wrapper with nowhere to attach extra top-level data. `TaskCollection` extends `ResourceCollection` explicitly so it can add a `status_summary` key alongside `data`:

```php
public function with(Request $request): array
{
    $allTasks = Task::all();
    // ...
    return ['status_summary' => [/* total + counts by status */]];
}
```

**A deliberate implementation choice worth explaining:** this recomputes from `Task::all()` directly rather than reading `$this->collection` — I'm not fully certain of `ResourceCollection`'s exact internal representation (raw items vs. already-wrapped resources) without `vendor/` available to verify, so going straight back to the model sidesteps that uncertainty entirely and is guaranteed correct either way.

### 4. Pagination's Real Shape, Built by Hand

`Task` isn't Eloquent yet, so there's no real database query to paginate. But the *response shape* — `links`, and `meta` with `current_page`/`last_page`/`per_page`/`total` — doesn't actually come from the query. It comes from Laravel's `LengthAwarePaginator` class:

```php
$paginator = new LengthAwarePaginator(
    $itemsForThisPage,
    count($allTasks),
    $perPage,
    $currentPage,
    ['path' => $request->url(), 'query' => $request->query()],
);

return new TaskCollection($paginator);
```

Constructing this manually, from a plain in-memory slice, is what reveals that a real Eloquent `->paginate()` call (arriving Week 3 onward) is convenient *sugar* around building this exact same object — not a fundamentally different mechanism. `TaskCollection` automatically detects its underlying resource is a paginator and adds `links`/`meta` on top of its own `data`/`status_summary` keys, with zero extra code needed in `TaskCollection` itself.

**Scope, stated directly:** deeper pagination methods — `simplePaginate()`, `cursorPaginate()`, and when to choose each — are explicitly **Week 4 Day 2**'s topic. Today only covers the shape one default, length-aware paginated response takes.

---

## 🖥️ Trying This Yourself

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan serve
```

```bash
# Paginated list — 2 per page by default
curl http://localhost:8000/api/tasks

# A different page size
curl "http://localhost:8000/api/tasks?per_page=1&page=2"

# Conditional attributes — compare these two
curl http://localhost:8000/api/tasks/1
curl "http://localhost:8000/api/tasks/1?debug=1&include_meta=1"
```

**The actual Day 5 exercise:** hit `/api/tasks` and look at the top-level response — `data` (from `TaskCollection::toArray()`), `status_summary` (from `TaskCollection::with()`), and `links`/`meta` (added automatically by Laravel because the underlying resource is a paginator) all coexist as three genuinely different sources contributing to one response, none of them stepping on each other.

---

## ❌ Common Mistakes

| Mistake | Fix |
|---------|-----|
| Calling `$model->toArray()` directly from multiple controller methods | Centralize through a `JsonResource` instead — one place to change the shape |
| Always including a field as `null` when it's not applicable | Use `when()` to omit the key entirely instead — a cleaner contract for consumers |
| Building an anonymous `::collection()` when you actually need collection-level metadata | Extend `ResourceCollection` explicitly — that's specifically what unlocks `with()` |
| Assuming pagination requires a real database query to produce its response shape | It doesn't — the shape comes from `LengthAwarePaginator` itself, constructible from any array |
| Diving into `simplePaginate()`/`cursorPaginate()` trade-offs today | That comparison is Week 4 Day 2's job — today only covers the default paginated shape |

---

## ✅ Day 5 Checklist

- [x] `TaskResource` — centralized transformation, replacing every scattered `->toArray()` call across the controller
- [x] `when()` and `mergeWhen()` — both conditional-attribute mechanisms demonstrated, driven by real query parameters
- [x] `TaskCollection` — a real `ResourceCollection` with genuine collection-level metadata (`status_summary`), not just a bare list
- [x] Manual `LengthAwarePaginator` construction — pagination's response shape understood as a real, reusable class, not database-query magic
- [x] `TaskResource` reused inside Day 3's `success()` macro in `markAsDone()`, proving it's a genuinely shared transformation, not tied to only plain CRUD

---

**Date**: September 13, 2026
**Status**: ✅ Week 2, Day 5 Complete!
**Next**: Day 6 — Postman Collection: testing every endpoint this project now exposes, environment variables, and automated tests within Postman itself.

> *"A resource class doesn't hide data from the response. It decides, in exactly one place, what 'the public shape of a task' means — so every consumer of this API sees the same answer."*
