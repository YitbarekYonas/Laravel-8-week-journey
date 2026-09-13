# Laravel Roadmap — Week 2, Day 3

[![Status](https://img.shields.io/badge/Status-Completed-brightgreen.svg)]()
[![Day](https://img.shields.io/badge/Day-3-blue.svg)]()
[![Topic](https://img.shields.io/badge/Topic-Responses-orange.svg)]()

> **"A response macro doesn't add a new capability. It adds a name for a shape you were already going to repeat."**

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

- ✅ Register a custom response macro, and understand exactly what `Response::macro()` does
- ✅ Review every HTTP status code this project uses so far, with an explicit reason for each
- ✅ Give `409 Conflict` a genuine reason to exist — a real state-transition rule, not a contrived example
- ✅ Read and write headers explicitly, two different ways

---

## 💡 What I Learned Today

### 1. What a Response Macro Actually Is

```php
Response::macro('success', function (mixed $data = null, string $message = 'OK', int $status = 200): JsonResponse {
    return response()->json(['success' => true, 'message' => $message, 'data' => $data], $status);
});
```

`Response::macro()` adds a genuinely new method to Laravel's response factory. After this runs once at boot, `response()->success(...)` works **exactly** like the built-in `response()->json(...)` this project has used since Day 1 — there's no functional difference between a macro and a method Laravel shipped with. It's registered in a small, dedicated `ResponseMacroServiceProvider`, in `boot()` by convention (not because `register()` would actually break anything here — nothing about registering a macro depends on another provider having run first, unlike Week 1 Day 3's real boot()-only rule for resolving another provider's binding. It's boot() purely because that's where Laravel's own conventions put this *kind* of setup, and keeping every provider's `register()` focused strictly on binding is a habit worth keeping even in the one case that doesn't strictly require it).

**Scope, stated honestly:** the paired `error()` macro is a lightweight preview of Week 6 Day 2's real topic — a proper global exception handler enforcing one consistent error shape across the *entire* app. Today's macro only standardizes the shape for the one place that calls it directly (`TaskController::markAsDone`). Laravel's own automatic `404`s (route model binding) and `422`s (Form Request validation) still use Laravel's own default shapes, completely untouched by this — unifying *everything* into one shape is deliberately not today's job.

### 2. Every Status Code This Project Uses, Reviewed With a Reason

| Code | Where | Why |
|---|---|---|
| `200 OK` | `index()`, `show()`, `update()` | Successful, with a response body the client needs |
| `201 Created` | `store()` | A new resource was created — paired with a `Location` header, per REST convention |
| `204 No Content` | `destroy()` | Successful, nothing to return |
| `404 Not Found` | *(automatic)* | Route model binding failed — never written explicitly anywhere (Week 2 Day 1) |
| `422 Unprocessable Entity` | *(automatic)* | Form Request validation failed — never written explicitly anywhere (Week 2 Day 2) |
| `409 Conflict` | `markAsDone()` | New today — see below |

`StatusCodeDemoController` (`GET /api/status-demo?code=XXX`) turns this table into something explorable — pass any code and get a real response actually carrying it, with a description of when it's the right choice.

### 3. `409 Conflict` — a Real Reason, Not a Contrived One

```php
public function markAsDone(Task $task): JsonResponse
{
    if ($task->status === 'DONE') {
        return response()->error("Task {$task->id} is already DONE.", 409);
    }
    if ($task->status === 'TODO') {
        return response()->error("Task {$task->id} must be IN_PROGRESS before it can be marked DONE.", 409);
    }

    $updated = Task::updateRecord($task->id, ['status' => 'DONE']);
    return response()->success($updated->toArray(), 'Task marked as done.');
}
```

A task can only reach `DONE` by first passing through `IN_PROGRESS` — not straight from `TODO`. This is deliberately **not** a `422` — the request itself is perfectly well-formed, no field is malformed. It's also not a `404` — the task genuinely exists. `409 Conflict` is the status code that means precisely this: a well-formed request, against a resource that exists, that conflicts with that resource's **current state**. This is also the first place in the project either new macro actually gets used, tying today's two subtopics — macros and status codes — together in one real method instead of two disconnected demos.

### 4. Headers, Both Directions

```php
$request->header('X-Custom-Header', '(default if absent)');   // reading
->header('X-Powered-By', 'value')                              // writing ONE
->withHeaders(['X-A' => '1', 'X-B' => '2'])                     // writing SEVERAL
```

This project already used headers incidentally — the `Location` header on `store()` (Day 2), `X-Request-Id` from `RequestIdMiddleware` (Week 1 Day 6). `HeaderDemoController` isolates the mechanism on its own, both reading an incoming header (with a fallback) and writing outgoing ones two different ways.

---

## 🖥️ Trying This Yourself

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan serve
```

```bash
# Explore any status code
curl -i "http://localhost:8000/api/status-demo?code=409"

# Headers, both directions
curl -i -H "X-Custom-Header: hello" http://localhost:8000/api/header-demo

# The real 409 — task 3 seeds as TODO, so this should be REJECTED
curl -i -X PATCH http://localhost:8000/api/tasks/3/done

# Task 2 seeds as IN_PROGRESS, so this should SUCCEED
curl -i -X PATCH http://localhost:8000/api/tasks/2/done
```

**The actual Day 3 exercise:** compare the JSON *shape* of a successful `markAsDone()` call (using the new `success()` macro) against a plain `response()->json($task->toArray())` from `show()` — notice the macro wraps the same task data in a consistent `{success, message, data}` envelope, while `show()` returns the task fields directly at the top level. Neither is "wrong" — they're just two different conventions, and this project hasn't unified them everywhere on purpose (see the scope note above).

---

## ❌ Common Mistakes

| Mistake | Fix |
|---------|-----|
| Using `422` for a state-conflict, or `409` for a validation failure | `422` = malformed input; `409` = well-formed request that conflicts with current state — they're answering different questions |
| Writing a duplicate `response()->json([...], $code)` block in every controller that needs the same shape | Register a macro once instead — same result, one place to change it later |
| Assuming a macro needs to live in `register()` | It's conventionally `boot()`, even when nothing technically requires it — consistency matters more than strict necessity here |
| Manually returning 404 after a failed lookup | Route model binding already does this automatically — don't duplicate what Laravel gives you for free |
| Forgetting the `Location` header on a `201` response | It's part of the REST convention for "here's the resource you just created" — not just a nice-to-have |

---

## ✅ Day 3 Checklist

- [x] `ResponseMacroServiceProvider` — `success()` and `error()` macros, registered and explained
- [x] Every status code this project uses reviewed in one place, with an explicit reason for each
- [x] `409 Conflict` given a genuine reason to exist — a real state-transition rule, not a contrived example
- [x] `StatusCodeDemoController` — an interactive, explorable review of status codes generally
- [x] `HeaderDemoController` — reading and writing headers, explicitly, both directions
- [x] Honest scope note on the `error()` macro — not Week 6's full exception-handling unification, and said so directly

---

**Date**: September 12, 2026
**Status**: ✅ Week 2, Day 3 Complete!
**Next**: Day 4 — Middleware: creating custom middleware for real (the deeper treatment Week 1 Day 6's `TraceMiddleware`/`RequestIdMiddleware` deliberately didn't attempt), terminable middleware, and middleware groups.

> *"200 different controllers all deciding independently how to shape a success response is 200 places that can quietly drift apart. One macro is one place."*
