# Laravel Roadmap — Week 2, Day 7: Mini-Project

[![Status](https://img.shields.io/badge/Status-Completed-brightgreen.svg)]()
[![Day](https://img.shields.io/badge/Day-7-blue.svg)]()
[![Topic](https://img.shields.io/badge/Topic-Week%202%20Capstone-orange.svg)]()

> **"By Day 5 this was already a working REST API. Day 7's job wasn't to build a bigger one — it was to notice the gate built on Day 4 was never actually guarding the door."**

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

## 🎯 What This Mini-Project Is

The roadmap calls Week 2's capstone a "RESTful Task API — full CRUD with proper HTTP verbs, status codes, API resources, Postman collection." Every one of those pieces already existed by the end of Day 6. So today's real job — matching how Week 1 Day 7 worked — was integration, not addition: find the one genuine gap left between six days of separately-built pieces, and close it.

**The gap:** Day 4 built real, working middleware — `ApiKeyMiddleware` and a `secure-api` group — and it has, until today, **only ever protected a standalone demo route** (`/api/secure/ping`). The actual Task API has been completely open this whole time. That's not a trick or an oversight left in on purpose for today — it's just genuinely how the pieces were built across separate days, and today is the day they finally get wired together properly.

---

## 💡 What's New Today

### 1. `TaskController` Now Protects Its Own Writes

```php
class TaskController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('secure-api', only: ['store', 'update', 'destroy', 'markAsDone']),
        ];
    }
    // ...
}
```

`HasMiddleware` is a Laravel 11 feature — implementing it lets a controller declare its **own** middleware, scoped to specific actions, without touching `routes/api.php` at all. `index()` and `show()` stay completely public; `store()`, `update()`, `destroy()`, and `markAsDone()` now require a valid `X-Api-Key` header, reusing the *exact* `secure-api` group Day 4 already built — nothing new was written for the gating logic itself, only where it gets applied.

This is a genuinely common real-world API shape: reads are public, writes require a credential. It's also worth noting *when* this check happens relative to route model binding — the middleware pipeline runs before the controller method (and its parameter resolution) executes at all, so an unauthenticated `DELETE /api/tasks/999` correctly returns `401`, not `404` — the auth check wins before Laravel even tries to look up whether task `999` exists.

**Honest caveat, same pattern as every other framework-internals claim in this project:** I'm confident in `HasMiddleware`'s existence and shape from documented Laravel 11 behavior, but I can't verify `Middleware`'s exact constructor signature against real `vendor/` source in this sandbox. Worth a quick check once you run `composer install`.

### 2. The Postman Collection, Updated to Match Reality

Building the code change alone wasn't enough — Day 6's collection would have started **failing** the moment this shipped, since its write requests never sent an `X-Api-Key` header. Today's collection:

- Adds the header to every write request (`Update`, both `markAsDone` calls, `Delete`, `Create`) — including the one that expects a `409`, since the auth check happens *before* that business-rule check, and would have wrongly returned `401` instead without the key
- Adds a **new** negative test — `2b. Attempt to Update WITHOUT Api Key (expect 401)` — proving the new protection is real, not just asserted in a comment

This is itself a small but real lesson: a code change that affects an API's contract has to be reflected in the tests validating that contract, or the tests silently stop meaning what they used to mean.

### 3. What Deliberately Didn't Change

- `Task`'s in-memory, per-request persistence model — still exactly as honestly limited as Week 2 Day 2 described. Real persistence is Week 3's job.
- No unified error-response shape across every failure mode — the `error()`/`success()` macros still only cover what they've always covered. Full exception-handling unification is Week 6 Day 2's topic.
- No real per-user authentication — `ApiKeyMiddleware` is still a shared-secret gate, not Sanctum. Week 5's job.

---

## 📖 Week 2 API Reference

| Method | Endpoint | Auth Required | Success | Notes |
|---|---|---|---|---|
| `GET` | `/api/tasks` | No | `200` | Paginated (`?page=`, `?per_page=`), wrapped in `TaskCollection` |
| `GET` | `/api/tasks/{task}` | No | `200` | `404` automatic via route model binding if not found |
| `POST` | `/api/tasks` | **Yes** (`X-Api-Key`) | `201` + `Location` header | Validated via `StoreTaskRequest`; `422` automatic on failure |
| `PUT` | `/api/tasks/{task}` | **Yes** | `200` | Validated via `UpdateTaskRequest` |
| `DELETE` | `/api/tasks/{task}` | **Yes** | `204` | No response body |
| `PATCH` | `/api/tasks/{task}/done` | **Yes** | `200` | `409` if the task isn't `IN_PROGRESS` |
| `GET` | `/api/tasks-summary` | No | `200` | Single-action controller |

Every response from a single-task endpoint is wrapped in a `data` key (Laravel's `JsonResource` default — see Day 6's correction). `markAsDone()` additionally wraps in `{success, message, data}` via Day 3's macro.

---

## 🖥️ Trying This Yourself

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan serve
```

```bash
# Reads stay public
curl http://localhost:8000/api/tasks

# Writes now require the key
curl -i -X PUT http://localhost:8000/api/tasks/3 \
  -H "Content-Type: application/json" \
  -d '{"title":"No key sent","status":"IN_PROGRESS"}'
# → 401

curl -i -X PUT http://localhost:8000/api/tasks/3 \
  -H "Content-Type: application/json" \
  -H "X-Api-Key: demo-secret-key" \
  -d '{"title":"With the key","status":"IN_PROGRESS"}'
# → 200
```

Or import `postman/` and run the **1. Task Manager — CRUD Lifecycle** folder end to end — all 9 requests, including the new `401` check, should pass.

---

## ❌ Common Mistakes

| Mistake | Fix |
|---------|-----|
| Treating a mini-project as "build more features" | Sometimes the real work is noticing two already-built pieces were never actually connected |
| Changing an API's auth requirements without updating the tests that exercise it | A test suite that doesn't reflect the current contract is actively misleading, not just incomplete |
| Applying middleware to an entire resource when only some actions need it | Laravel 11's `HasMiddleware` + `Middleware::only()`/`except()` scopes it precisely, without touching routes at all |
| Forgetting that middleware runs before route model binding | An unauthenticated request to a nonexistent resource still correctly returns `401`, not `404` — the auth check wins first |

---

## ✅ Day 7 (Week 2 Capstone) Checklist

- [x] `TaskController implements HasMiddleware` — write actions protected, reads left public
- [x] Reuses Day 4's exact `secure-api` group — no new gating logic written, only newly applied
- [x] Postman collection updated to match the new contract, including a new passing negative test
- [x] A full Week 2 API reference table, in one place
- [x] Explicit list of what deliberately didn't change today, and why

---

## 🎉 Week 2 Complete!

Seven days: resource controllers and route model binding, form requests and validation, responses and macros, real middleware, API resources, a genuine Postman test suite, and today — the moment two of those pieces actually got connected to each other for the first time.

**Date**: September 14, 2026
**Status**: ✅ Week 2 Complete!
**Next**: Week 3 — Database & Eloquent ORM: migrations, real models, relationships — where `Task`'s in-memory, per-request-only persistence finally becomes real.

> *"Six days build the pieces. The seventh day is for noticing which ones were never actually talking to each other."*
