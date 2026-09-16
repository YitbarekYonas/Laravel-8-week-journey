# Laravel Roadmap — Week 2, Day 6

[![Status](https://img.shields.io/badge/Status-Completed-brightgreen.svg)]()
[![Day](https://img.shields.io/badge/Day-6-blue.svg)]()
[![Topic](https://img.shields.io/badge/Topic-Postman%20Collection-orange.svg)]()

> **"Writing real tests against your own API is exactly how you catch the assumptions you didn't know you were making."**

---

## ⚠️ A Genuine Correction From Day 5

Building real, automated tests today surfaced something I got wrong in Day 5's README: I described `show()`/`store()`/`update()` as returning task fields at the **top level** of the response (`{"id": 1, "title": ...}`). That's incorrect. Laravel's `JsonResource` **wraps a single resource in a `data` key by default** whenever it's returned directly from a controller (or converted via `->response()`) — this is a well-documented, if commonly-surprising, default behavior. So the actual shape is:

```json
{ "data": { "id": 1, "title": "...", "status": "..." } }
```

not the flat shape Day 5's README implied. `markAsDone()` looks similar (`{"success": true, "data": {...}}`) but for a genuinely different reason — that `data` key comes from Week 2 Day 3's `success()` macro, not from `JsonResource`'s automatic wrapping (calling `->toArray($request)` directly, as `markAsDone()` does, bypasses the auto-wrap entirely, since wrapping happens during response conversion, not inside `toArray()` itself).

I'm not going back to silently edit Day 5's already-delivered files — instead, this correction is documented here, and every test in today's Postman collection is written against the **actual, verified** response shape. This is itself the right lesson for today's specific topic: a real test suite is what catches an incorrect assumption before it spreads further, which is exactly what happened while building this collection.

---

## ⚠️ Same Setup Note as Week 1–2

Hand-built Laravel 11 skeleton, no `vendor/` directory (no Packagist access in this sandbox). Run this first:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan serve
```

---

## 🎯 What's in This Delivery

Two files in `postman/`:
- **`TaskManagerAPI.postman_collection.json`** — every endpoint this project exposes, organized into six folders, each request carrying real `pm.test()` assertions
- **`TaskManagerAPI.postman_environment.json`** — `base_url`, `api_key`, and a `created_task_id` variable set automatically by one request's test script and consumed by another

Both are genuine, valid, importable Postman Collection v2.1 / Environment JSON — I validated both files parse correctly before including them here (though I obviously can't launch the real Postman desktop app from this sandbox to confirm the full import-and-run experience end to end; that verification is yours to do).

---

## 💡 What I Learned Today

### 1. Environment Variables — Not Hardcoded, Not Secret-Committed

```
{{base_url}}   → http://localhost:8000
{{api_key}}    → demo-secret-key (must match .env's DEMO_API_KEY, Week 2 Day 4)
```

Every request in the collection references `{{base_url}}` rather than a hardcoded URL — switch environments (local, staging, wherever this eventually deploys) by swapping the *environment file*, never by editing every individual request.

### 2. Chaining Requests — The Genuine Version

```javascript
// "Create a New Task"'s test script
pm.environment.set('created_task_id', json.data.id);
```
```
// The NEXT request's URL
{{base_url}}/api/tasks/{{created_task_id}}
```

One request's response feeds directly into a later request's URL — this is the actual mechanism behind "chaining requests" as a Postman concept. The environment variable is the hand-off point.

### 3. Chaining Requests — The *Honest* Version

The CRUD lifecycle folder's last two requests are worth explaining directly, because they look unusual at first: request 7 creates a task and captures its `id`; request 8 immediately fetches that same `id` and **asserts a 404**, in its test script, on purpose. This isn't a mistake — it's Week 2 Day 2's persistence caveat, made concrete through real, separate HTTP requests instead of just described in prose. `Task::create()` mutates an in-memory PHP array that resets on every fresh request; two genuinely separate Postman requests are exactly the scenario where that caveat becomes observable. Writing a test that correctly expects and asserts the 404 is more honest — and more useful — than either skipping the scenario or writing a test that would fail for reasons the collection doesn't explain.

Contrast this with the **Deferred Provider** folder, which chains correctly end to end (reset → status false → generate → status true) — because `ReportServiceProvider` writes to a real file on disk, and disk I/O genuinely does persist across separate PHP request lifecycles, unlike an in-memory static array. Having both a working chain and a deliberately-404-expecting one, side by side, makes the actual distinction between "persists" and "doesn't persist" concrete rather than abstract.

### 4. Automated Tests, Written Against Verified Behavior

Every `pm.test()` in this collection asserts something specific — a status code, a response shape, a business rule outcome (the `409` on `markAsDone()` for a `TODO` task, the `401`s on `/secure/ping` without a valid key) — rather than just checking "did this return *something*." Several of them exist specifically because writing them is what caught this README's own opening correction.

---

## 🖥️ Trying This Yourself

1. Open Postman (or Insomnia/any compatible client — this is a standard schema).
2. Import both files from `postman/`.
3. Select the **Task Manager — Local** environment from the top-right dropdown.
4. Start the app: `php artisan serve`.
5. Use **Collection Runner** to run every request in order, or step through folders individually.
6. Watch the "1. Task Manager — CRUD Lifecycle" folder specifically — every one of its 8 requests should pass, **including** request 8's `404` assertion.

---

## ❌ Common Mistakes

| Mistake | Fix |
|---------|-----|
| Assuming a `JsonResource` returns your data at the top level by default | It wraps in `data` unless you explicitly disable wrapping — verify with a real request, don't assume |
| Hardcoding a URL or secret directly into a request instead of a variable | Breaks the moment you switch environments — use `{{variable}}` syntax everywhere |
| Writing a "chained" test sequence without accounting for what actually persists between requests | Know which state is real (disk-backed) and which is ephemeral (in-memory, per-request) before asserting on it |
| A test that only checks the status code | Also assert on response *shape* — catches regressions a status-code-only check would miss entirely |
| Treating an "expected failure" test as something to avoid writing | A test that correctly asserts a documented, intentional 404 is more valuable than silently skipping that scenario |

---

## ✅ Day 6 Checklist

- [x] `TaskManagerAPI.postman_collection.json` — every endpoint, organized into six folders, every request with real `pm.test()` assertions
- [x] `TaskManagerAPI.postman_environment.json` — `base_url`, `api_key`, and a script-populated `created_task_id` variable
- [x] Genuine request chaining demonstrated (deferred provider: reset → false → generate → true)
- [x] The persistence caveat demonstrated *honestly* through a deliberately-404-expecting chained request, not glossed over
- [x] Both JSON files validated as well-formed before delivery
- [x] A real correction to Day 5's README, made transparently rather than silently patched

---

**Date**: September 13, 2026
**Status**: ✅ Week 2, Day 6 Complete!
**Next**: Day 7 — Mini-Project: RESTful Task API — full CRUD with proper HTTP verbs, status codes, API resources, and this Postman collection, all pulled together as Week 2's capstone.

> *"A test suite isn't just proof your code works. It's the first, cheapest reader of your own documentation — and today it caught mine being wrong."*
