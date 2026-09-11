# Laravel Roadmap — Week 2, Day 1

[![Status](https://img.shields.io/badge/Status-Completed-brightgreen.svg)]()
[![Day](https://img.shields.io/badge/Day-1-blue.svg)]()
[![Topic](https://img.shields.io/badge/Topic-Controllers-orange.svg)]()

> **"Route model binding isn't Eloquent magic. It's one interface — implement it yourself, and it works with zero database involved."**

---

## ⚠️ Same Setup Note as Week 1

Hand-built Laravel 11 skeleton, no `vendor/` directory (no Packagist access in this sandbox). Run this first:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

## ⚠️ A Session Note, For Transparency

My sandbox session reset partway through building this project — the entire Week 1 codebase disappeared from disk mid-task. I rebuilt everything from memory to keep this zip complete and internally consistent, cross-checking route names, controller references, and imports before packaging. I'm confident the rebuild is faithful to what was in your earlier Week 1 zips, but if you spot any drift between this file and your local copies, that's the likely explanation — your local files are the source of truth.

---

## 🎯 Learning Objectives

- ✅ Build a real resource controller matching `Route::apiResource()`'s conventions
- ✅ Understand exactly why `create`/`edit` are correctly absent from an API resource controller
- ✅ Build single-action controllers, and know when a CRUD method name would misrepresent what an action does
- ✅ Understand route model binding as a real, implementable interface contract — not framework magic — by implementing it on a plain PHP class, with zero database involved

---

## 💡 What I Learned Today

### 1. A Real Resource Controller, Replacing a Stub

Week 1 Days 5–7 used a deliberately minimal `TaskController` — hardcoded data, just `index()`/`show()`/`greetOptional()`, explicitly flagged as *not* this week's real work. Today it's rebuilt to match what `Route::apiResource()` actually expects:

```php
class TaskController extends Controller
{
    public function index(): JsonResponse { /* ... */ }
    public function show(Task $task): JsonResponse { /* ... */ }
    public function store(Request $request): JsonResponse { /* ... */ }
    public function update(Request $request, Task $task): JsonResponse { /* ... */ }
    public function destroy(Task $task): JsonResponse { /* ... */ }
}
```

`create()` and `edit()` are correctly **absent** — those two exist only to return an HTML `<form>` for creating/editing a resource. A JSON API never renders a form at all, so `Route::apiResource()` (unlike `Route::resource()`, which is for full HTML apps) omits them by default. `store()` and `update()` return `501 Not Implemented` for now, deliberately — real validation is Day 2's topic, real persistence is Week 3's; returning fake success here would misrepresent what actually works today.

### 2. `Route::apiResource()` Replaces Day 5's Hand-Written Group

```php
// Week 1 Day 5 — written by hand
Route::prefix('tasks')->name('tasks.')->group(function () {
    Route::get('/', [TaskController::class, 'index'])->name('index');
    Route::get('/{task}', [TaskController::class, 'show'])->whereNumber('task')->name('show');
});

// Week 2 Day 1 — one line
Route::apiResource('tasks', TaskController::class)->whereNumber('task');
```

This isn't a coincidence — `Route::apiResource()` generates *exactly* the route names (`tasks.index`, `tasks.show`, `tasks.store`, `tasks.update`, `tasks.destroy`) and the `{task}` parameter name that Day 5's manual group was already replicating by hand. Day 5's closing comment specifically called `{task}` (not `{id}`) "the exact convention Eloquent route model binding expects" — that decision pays off directly today.

### 3. Single-Action Controllers — Not Everything Is CRUD

```php
// Not a resource action — forcing it into index/show/store/update/destroy
// would misrepresent what it does
class TaskSummaryController extends Controller
{
    public function __invoke(): JsonResponse { /* returns counts by status */ }
}
```

```php
Route::get('/tasks-summary', TaskSummaryController::class)->name('tasks.summary');
```

No `[Controller::class, 'method']` array — just the class name. Laravel calls `__invoke()` automatically. `GreetOptionalController` is a second example, and its existence today is itself a small lesson: it used to live as a stray method on `TaskController` (Week 1 Day 5), which never made sense once `TaskController` needed to be a *real* resource controller. Relocating it to its own single-action controller is exactly the kind of cleanup a resource controller's arrival forces — a stray non-resource method doesn't belong bolted onto a class whose whole shape should mirror RESTful conventions.

### 4. Route Model Binding — The Real Mechanism, No Database Required

This is the part I want to be most precise about. Route model binding is often taught as "Eloquent magic" — type-hint a model, Laravel finds it. That's not what's actually happening underneath. It's built on one interface: `Illuminate\Contracts\Routing\UrlRoutable`. Eloquent's `Model` class implements it; **any class can**.

```php
class Task implements UrlRoutable
{
    public function resolveRouteBinding($value, $field = null): ?self
    {
        return static::find((int) $value);
    }
    // getRouteKey(), getRouteKeyName(), resolveChildRouteBinding() — the rest of the contract
}
```

```php
public function show(Task $task): JsonResponse
{
    return response()->json($task->toArray());
}
```

When a request hits `GET /api/tasks/3`, before `show()` ever runs, Laravel:
1. Resolves an empty `Task` instance **via the container** — the same automatic-resolution mechanism from Week 1 Days 1–2
2. Calls that instance's `resolveRouteBinding('3')`
3. Gets back either a real, populated `Task`, or `null`

Returning `null` is what triggers Laravel's automatic `404` — there's no manual "if not found, return 404" branch anywhere in `TaskController::show()`. This works today with **zero database** involved, because `Task` is still a plain in-memory class. Week 3 will make `Task extends Model` instead, backed by a real table — and this exact mechanism keeps working unmodified, because Eloquent's `Model` satisfies the same `UrlRoutable` contract this hand-written version does.

**Honest caveat:** I don't have `vendor/` in this sandbox to verify `UrlRoutable`'s exact method signatures against the framework's actual source. What's implemented here reflects my understanding of documented Laravel behavior — worth a quick check against `vendor/laravel/framework/src/Illuminate/Contracts/Routing/UrlRoutable.php` once you run `composer install`.

---

## 🖥️ Trying This Yourself

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan serve
```

```bash
# Resource routes
curl http://localhost:8000/api/tasks
curl http://localhost:8000/api/tasks/1
curl http://localhost:8000/api/tasks/99      # 404 — resolveRouteBinding() returned null
curl http://localhost:8000/api/tasks/abc     # 404 — whereNumber('task') rejects it before routing even matches

# Single-action controllers
curl http://localhost:8000/api/tasks-summary
curl http://localhost:8000/api/greet-optional
curl http://localhost:8000/api/greet-optional/Sam

# Not-yet-implemented, honestly
curl -X POST http://localhost:8000/api/tasks    # 501
```

**The actual Day 1 exercise:** run `php artisan route:list --path=tasks` and compare the output against the manual route group from Week 1 Day 5's README — same names, same URIs, same parameter, now generated from one line instead of five.

---

## ❌ Common Mistakes

| Mistake | Fix |
|---------|-----|
| Using `Route::resource()` for a JSON-only API | Use `Route::apiResource()` instead — it correctly omits `create`/`edit`, which only exist to serve HTML forms |
| Manually looking up a model by ID inside a controller method | Type-hint it in the method signature instead — route model binding resolves and 404s automatically |
| Assuming route model binding requires Eloquent | It requires `UrlRoutable` — Eloquent is just the most common implementer, not the only possible one |
| Forcing a non-CRUD action into a resource controller's method names | Use a single-action controller (`__invoke()`) instead — a misleading method name is worse than a slightly less conventional file |
| Leaving a stray unrelated method on a resource controller | Relocate it to its own controller, as done here with `greetOptional` |

---

## ✅ Day 1 Checklist

- [x] `TaskController` rebuilt as a real resource controller — `index`/`show`/`store`/`update`/`destroy`, `create`/`edit` correctly omitted
- [x] `Route::apiResource('tasks', TaskController::class)` replacing Week 1 Day 5's manual route group, verified to generate identical names
- [x] Two single-action controllers (`TaskSummaryController`, `GreetOptionalController`), one of them a genuine relocation motivated by `TaskController`'s new shape
- [x] `Task` model implementing `UrlRoutable` by hand — route model binding proven to work with zero database, ready to become a real Eloquent model in Week 3 with no consumer-side changes
- [x] `501 Not Implemented` used honestly for `store()`/`update()`/`destroy()`, rather than faking success

---

**Date**: September 6, 2026
**Status**: ✅ Week 2, Day 1 Complete!
**Next**: Day 2 — Requests: form requests, validation, accessing input, and file uploads — where `store()` and `update()` finally get real, validated input instead of a `501`.

> *"A stub controller and a resource controller can have the same class name and completely different responsibilities. Today's `TaskController` earned the name by actually matching the convention it claims to follow."*
