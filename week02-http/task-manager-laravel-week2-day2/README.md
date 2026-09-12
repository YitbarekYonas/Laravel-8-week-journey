# Laravel Roadmap — Week 2, Day 2

[![Status](https://img.shields.io/badge/Status-Completed-brightgreen.svg)]()
[![Day](https://img.shields.io/badge/Day-2-blue.svg)]()
[![Topic](https://img.shields.io/badge/Topic-Requests%20%26%20Validation-orange.svg)]()

> **"A Form Request isn't a validation helper bolted onto a controller. It's a gate the request has to pass through before the controller method is even allowed to run."**

---

## ⚠️ Same Setup Note as Week 1

Hand-built Laravel 11 skeleton, no `vendor/` directory (no Packagist access in this sandbox). Run this first:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

## ⚠️ The Most Important Caveat in This Whole Project

Before anything else: **`Task::create()` today does NOT persist a new task across separate HTTP requests.** This isn't a bug — it's an honest, deliberate consequence of PHP's execution model that's worth understanding properly rather than glossing over.

Coming from a long-running process (a JVM running your Spring Boot app, a Node.js server), it's natural to assume an in-memory array behaves the same way in PHP: mutate it once, and every future request sees the update. **That assumption is wrong for standard PHP.** Under the normal `php-fpm` / `php artisan serve` execution model, **the entire application boots fresh on every single HTTP request** — `public/index.php` re-runs from the top, `bootstrap/app.php` re-executes, every service provider re-registers, and every class (including `Task`, and its `private static array $records`) is freshly interpreted with its properties reset to whatever their source code declares. A `Task::create()` call during one request is completely real and correctly validated *within that request* — the response you get back is accurate — but the very next `GET /api/tasks` boots an entirely new script execution, and `$records` is back to its original three hardcoded entries.

This is precisely why Week 3 exists: real persistence across requests needs an actual datastore (a database via Eloquent) — not a cleverer in-memory structure, because *no* in-memory PHP structure survives across separate requests under this execution model.

---

## 🎯 Learning Objectives

- ✅ Understand what a Form Request actually is, and why validation belongs there instead of inside the controller method
- ✅ Write real validation rules, including file validation
- ✅ Understand the difference between `$request->validated()` and every other way of reading input
- ✅ Handle a file upload end to end — validate it, store it, get back a usable path
- ✅ Understand precisely why "in-memory create" doesn't mean the same thing in PHP that it means in a long-running process

---

## 💡 What I Learned Today

### 1. A Form Request Is a Gate, Not a Helper

```php
class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:100'],
            'status' => ['required', 'string', 'in:TODO,IN_PROGRESS,DONE'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ];
    }
}
```

```php
public function store(StoreTaskRequest $request): JsonResponse { /* ... */ }
```

Type-hinting `StoreTaskRequest` instead of the base `Request` is what actually triggers validation — Laravel validates the incoming data against `rules()` **before** `store()`'s method body ever runs. A request that fails validation never reaches this method at all; Laravel automatically returns a `422` with a structured `{"message": ..., "errors": {...}}` body. There is no `if (!valid) { return error }` branch anywhere in `TaskController` — the Form Request *is* that branch, happening earlier and automatically.

`authorize()` is a separate question from validity: it decides whether this request is allowed to be attempted *at all*, independent of whether the data in it is well-formed. Returning `true` here means "anyone can attempt this" — real per-user authorization (only the task's owner, only an admin) is Week 5–6 territory, not built yet.

### 2. `->validated()` vs Every Other Way to Read Input

```php
$validated = $request->validated();   // ONLY the fields declared in rules() — trusted
$request->all();                      // EVERYTHING the client sent — untrusted
$request->only(['title', 'status']);  // just these keys, present or not
$request->input('priority', 'MEDIUM'); // one key, with a fallback
$request->has('title');               // present? (even if empty string)
$request->filled('title');            // present AND non-empty?
```

`InputAccessDemoController` puts all of these side by side on one endpoint, deliberately *not* tied to any Form Request — it accepts anything and shows exactly what each accessor returns for the same request body. `TaskController::store()` uses `->validated()` specifically *because* it needs to trust what it's about to persist; `->all()` would include anything a client sent, validated or not, which is exactly the kind of unchecked input that shouldn't reach a datastore.

### 3. File Uploads, End to End

```php
'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
```

```php
if ($request->hasFile('attachment')) {
    $attachmentPath = $request->file('attachment')->store('task-attachments');
}
```

`mimes:jpg,jpeg,png,pdf` validates the uploaded file's actual type (not just its claimed extension); `max:2048` validates its size in kilobytes. Once validation passes, `->store('task-attachments')` saves it to the `local` disk (configured in `config/filesystems.php`) under a generated, collision-safe filename — never the client's original filename directly — and returns the relative path actually used. That path is what gets saved onto the `Task` record, not the file's original name or its temporary upload location.

### 4. `PUT` vs `PATCH` — a Distinction Worth Knowing, Not Built Today

`UpdateTaskRequest`'s rules are nearly identical to `StoreTaskRequest`'s — deliberately, since this project's update route is `PUT` (a full replacement, per REST semantics, which is what `Route::apiResource()` wires up by default). A `PATCH`-style partial update, where every field is optional via Laravel's `sometimes` validation rule, would be a genuinely different contract — not just a smaller version of this one. Worth knowing the distinction exists; not something this project needed to build today.

---

## 🖥️ Trying This Yourself

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan serve
```

```bash
# Real validation failure — 422, with structured field errors
curl -X POST http://localhost:8000/api/tasks \
  -H "Content-Type: application/json" \
  -d '{"title": ""}'

# Real success — 201, Location header, the created task in the body
curl -i -X POST http://localhost:8000/api/tasks \
  -H "Content-Type: application/json" \
  -d '{"title": "Write the Day 2 README", "status": "DONE"}'

# File upload — validated, stored, path returned
curl -X POST http://localhost:8000/api/tasks \
  -F "title=Upload a screenshot" \
  -F "status=TODO" \
  -F "attachment=@/path/to/a/real/image.png"

# Accessing input, every way, side by side
curl -X POST http://localhost:8000/api/input-demo \
  -d "title=Sam&status=TODO&extra_field=whatever"
```

**The actual Day 2 exercise — proving the persistence caveat for real:**
1. `curl -X POST http://localhost:8000/api/tasks -d '{"title":"Ghost task","status":"TODO"}' -H "Content-Type: application/json"` — note the returned `id` in the `201` response.
2. `curl http://localhost:8000/api/tasks` — the task you just created is **not** in this list. Same app, same code, same in-memory array — but a genuinely separate script execution.
3. This is the exact gap Week 3's Eloquent + real database closes.

---

## ❌ Common Mistakes

| Mistake | Fix |
|---------|-----|
| Validating manually inside the controller method | Use a Form Request instead — validation should happen before the method body runs, not as its first few lines |
| Using `$request->all()` to build data you're about to persist | Use `$request->validated()` — only fields your rules actually declared and checked |
| Trusting a client-supplied filename for where a file gets stored | Let `->store()` generate the filename; never build a storage path from user input directly |
| Assuming an in-memory PHP array persists across requests like it would in a long-running JVM process | It doesn't, under the standard PHP execution model — real cross-request persistence needs a real datastore |
| Building a "partial update" endpoint that's really just optional-everything with no real semantic difference from creation | Understand the `PUT` (full replace) vs `PATCH` (partial) distinction even if you only implement one today |

---

## ✅ Day 2 Checklist

- [x] `StoreTaskRequest` / `UpdateTaskRequest` — real Form Requests, `authorize()` and `rules()` both implemented
- [x] `TaskController::store()`/`update()`/`destroy()` — real, honestly-scoped implementations, replacing Day 1's `501`s
- [x] File upload validated (`mimes`, `max`) and stored via `config/filesystems.php`'s `local` disk
- [x] `InputAccessDemoController` — every common input-reading method demonstrated side by side on one endpoint
- [x] The PHP request-lifecycle persistence caveat explained precisely, and proven with a real two-request exercise, not just asserted

---

**Date**: September 6, 2026
**Status**: ✅ Week 2, Day 2 Complete!
**Next**: Day 3 — Responses: JSON responses, response macros, HTTP status codes, and headers — a closer look at everything `TaskController` has been returning all along.

> *"The most honest thing a demo project can do, when it hits a real limitation, is prove the limitation instead of quietly working around it."*
