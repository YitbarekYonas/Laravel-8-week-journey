# Laravel Roadmap — Week 1, Day 5

[![Status](https://img.shields.io/badge/Status-Completed-brightgreen.svg)]()
[![Day](https://img.shields.io/badge/Day-5-blue.svg)]()
[![Topic](https://img.shields.io/badge/Topic-Routing-orange.svg)]()

> **"A hardcoded URL string is a promise your code has to keep manually, forever. A named route is a promise the framework keeps for you."**

---

## ⚠️ Same Setup Note as Days 1–4

Hand-built Laravel 11 skeleton, no `vendor/` directory (no Packagist access in this sandbox). Run this first:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

---

## 🎯 Learning Objectives

- ✅ Required, constrained, and optional route parameters
- ✅ Named routes, and what `route()` actually buys you over a hardcoded URL
- ✅ Route groups — prefix, name-prefix, and middleware, applied once instead of repeated per-route
- ✅ Applying Laravel's *built-in* middleware (rate limiting) to a group, without writing custom middleware yet
- ✅ See every prior day's demo routes reorganized into a real, navigable structure

---

## 💡 What I Learned Today

### 1. Route Parameters — Required, Constrained, Optional

```php
// Required, unconstrained (Day 1's original)
Route::get('/greet/{name}', [GreetingController::class, 'greet']);

// Required, CONSTRAINED — must be one or more digits
Route::get('/{task}', [TaskController::class, 'show'])->whereNumber('task');

// OPTIONAL — the trailing ? makes the segment itself optional
Route::get('/greet-optional/{name?}', [TaskController::class, 'greetOptional']);
```

`->whereNumber('task')` isn't validation happening *inside* the controller — it's a routing-layer gate. `GET /api/tasks/abc` never reaches `TaskController::show()` at all; Laravel returns `404` before the controller is even instantiated. This matters because it means `show(int $task)` never has to defensively check "is this actually numeric" — the routing layer already guaranteed it by the time the method runs.

The optional parameter (`{name?}`) needs a matching PHP-level default in the controller method signature (`?string $name = 'friend'`) — the `?` in the route just makes the URL segment itself skippable; the fallback value still has to come from somewhere.

### 2. Named Routes — What They Actually Buy You

Every route in this project now has a `->name(...)`. The payoff isn't cosmetic — it's `RouteLinksController`, which generates real URLs purely from names + parameters:

```php
route('tasks.show', ['task' => 3])
// → http://localhost:8000/api/tasks/3
```

**Try this:** rename `Route::get('/tasks', ...)` to `Route::get('/task-items', ...)` in `routes/api.php`, keep `->name('index')` exactly as-is, and hit `/api/route-links` again. The generated URL updates automatically to `/api/task-items` — zero changes needed in `RouteLinksController`. A hardcoded URL string (`'/api/tasks'` typed literally somewhere) would have silently gone stale the moment the URI changed, with nothing anywhere to catch it.

### 3. Route Groups — Prefix, Name, and Middleware Together

Days 1–4's routes were a flat, ungrouped list by the time Day 4 ended. Today they're reorganized:

```php
Route::prefix('demo')->name('demo.')->group(function () {
    Route::get('/greet/{name}', [GreetingController::class, 'greet'])->name('greet');
    // ...
    Route::prefix('deferred')->name('deferred.')->group(function () {
        Route::get('/status', [DeferredDemoController::class, 'status'])->name('status');
        // → full name: demo.deferred.status, full URI: /api/demo/deferred/status
    });
});
```

Groups nest cleanly — the inner `deferred` group's prefix and name-prefix stack on top of the outer `demo` group's, producing `demo.deferred.status` and `/api/demo/deferred/status` from two small, readable declarations instead of one long repeated string per route.

### 4. Middleware on a Group — Built-In, No Custom Class Needed Yet

```php
Route::middleware('throttle:api-demo')->prefix('limited')->name('limited.')->group(function () {
    Route::get('/ping', function () { /* ... */ })->name('ping');
});
```

`throttle:api-demo` references a **named rate limiter**, defined once in `AppServiceProvider::boot()`:

```php
RateLimiter::for('api-demo', function (Request $request) {
    return Limit::perMinute(30)->by($request->ip());
});
```

This is Laravel's *built-in* throttling middleware, configured — genuinely no custom middleware class involved. Writing your own middleware from scratch is explicitly **Week 2 Day 4**'s topic; today's lesson is narrower — that middleware can be applied to a whole group at once, declared in one place, rather than repeated on every individual route.

---

## 🖥️ Trying This Yourself

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan serve
```

```bash
# Required + constrained parameter
curl http://localhost:8000/api/tasks/1
curl http://localhost:8000/api/tasks/abc      # 404 — never reaches the controller

# Optional parameter, both forms
curl http://localhost:8000/api/greet-optional
curl http://localhost:8000/api/greet-optional/Sam

# Named routes generating real URLs
curl http://localhost:8000/api/route-links

# Group middleware — hit this 31+ times inside a minute
curl http://localhost:8000/api/limited/ping
```

**The actual Day 5 exercise:** in `routes/api.php`, change `Route::get('/', [TaskController::class, 'index'])` to a different URI (keep the `->name('index')` line unchanged), then hit `/api/route-links` again — the `tasks_index` link updates automatically with zero other code touched. Also useful: run `php artisan route:list` to see every route this project now defines, including the full generated names and URIs after all the group nesting.

---

## ❌ Common Mistakes

| Mistake | Fix |
|---------|-----|
| Hardcoding URL strings instead of using `route()` | A renamed/moved URI silently breaks every hardcoded reference, with nothing to catch it — `route()` updates automatically |
| Validating a route parameter's format *inside* the controller | Use `->where()`/`->whereNumber()` at the routing layer instead — invalid requests never reach the controller at all |
| Forgetting an optional route parameter still needs a PHP-level default | `{name?}` alone doesn't supply a fallback value — the controller method signature has to |
| Repeating the same middleware on every individual route | Group them with `Route::middleware(...)->group(...)` instead — one declaration, applied to everything inside |
| Confusing route *names* with route *URIs* | They're independent — a name is a permanent label; the URI underneath can change freely as long as the name doesn't |

---

## ✅ Day 5 Checklist

- [x] Required route parameter (Day 1's original, still present)
- [x] Constrained route parameter (`->whereNumber('task')`) — invalid input rejected before the controller runs
- [x] Optional route parameter (`{name?}` + a PHP-level default)
- [x] Every route named, including nested group name-prefixing (`demo.deferred.status`)
- [x] `RouteLinksController` — proof that `route()` generates real, working URLs, not just labels
- [x] A route group using `->prefix()` + `->name()` together
- [x] A route group using built-in `throttle` middleware, configured via a named `RateLimiter::for(...)` rule
- [x] Every Week 1 Day 1–4 demo route reorganized into the new grouped structure, nothing lost

---

**Date**: August 30, 2026
**Status**: ✅ Week 1, Day 5 Complete!
**Next**: Day 6 — Request Lifecycle: the HTTP kernel, the middleware pipeline in full depth, and exactly how a request travels from `public/index.php` to a controller and back.

> *"Route groups don't add a new capability routes didn't already have. They just stop you from repeating yourself about the capability you're already using."*
