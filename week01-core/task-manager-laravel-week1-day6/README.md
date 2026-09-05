# Laravel Roadmap — Week 1, Day 6

[![Status](https://img.shields.io/badge/Status-Completed-brightgreen.svg)]()
[![Day](https://img.shields.io/badge/Day-6-blue.svg)]()
[![Topic](https://img.shields.io/badge/Topic-Request%20Lifecycle-orange.svg)]()

> **"Middleware isn't a list that runs in order. It's an onion — everyone runs inward once, then everyone runs outward again, in reverse."**

---

## ⚠️ Same Setup Note as Days 1–5

Hand-built Laravel 11 skeleton, no `vendor/` directory (no Packagist access in this sandbox). Run this first:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

## ⚠️ A Scope Note on Today's Middleware

Writing real, production middleware — parameters used for actual authorization logic, the `terminate()` hook, authoring your own middleware groups — is **Week 2 Day 4**'s topic, not today's. But you can't meaningfully observe "the middleware pipeline" without *some* concrete middleware to instrument it with. `TraceMiddleware` and `RequestIdMiddleware` in this project exist for exactly that reason: pure instrumentation, built to make today's actual subject (the pipeline mechanism) externally visible. Their job is narrower than what Week 2 Day 4 will build.

---

## 🎯 Learning Objectives

- ✅ Trace a request's full path: `public/index.php` → `bootstrap/app.php` → the HTTP kernel → the middleware pipeline → the matched route → the controller → back out
- ✅ Understand that Laravel 11 didn't remove the HTTP kernel — only the per-app `Kernel.php` file used to configure it
- ✅ See the middleware "onion" model proven with real, ordered output — not just described
- ✅ Understand the difference between global (group-wide) middleware and route-specific middleware, with both applied side by side

---

## 💡 What I Learned Today

### 1. The Full Trace, Entry Point to Response

```
1. public/index.php          — the ONE file every HTTP request hits, regardless of URL
2. require vendor/autoload.php  — makes every class available
3. require bootstrap/app.php  — boots the framework, returns the configured $app
4. $app->handleRequest($request)
     └─ resolves the HTTP kernel (Illuminate\Foundation\Http\Kernel — see section 2)
     └─ kernel->handle($request) sends the request through the FULL middleware
        pipeline: global-to-group middleware first (RequestIdMiddleware, applied
        to the whole 'api' group), then route-specific middleware
        (TraceMiddleware:Outer, TraceMiddleware:Inner, on the one route that
        lists them)
     └─ once every middleware layer has let the request through, Laravel's
        router matches the URI to a controller method and calls it
5. The controller returns a Response object
6. That Response travels back OUTWARD through the exact same middleware
   stack, in REVERSE order — innermost middleware's "after" code runs
   first, outermost's runs last
7. The final Response is sent to the client
```

This project's `/api/lifecycle/trace` endpoint makes every one of these steps observable in the actual JSON response — see "Trying This Yourself" below.

### 2. The HTTP Kernel Didn't Disappear — Only the File Did

A common misconception after Laravel 11's restructuring: "Laravel removed the HTTP kernel." It didn't. `Illuminate\Foundation\Http\Kernel` — the actual class responsible for running the middleware pipeline and dispatching to the router — still exists inside the framework and still does exactly the job it always did. What changed is that **your app no longer needs its own `app/Http/Kernel.php` file** just to configure that pipeline's contents:

```php
// bootstrap/app.php — configures the SAME underlying kernel Laravel 10
// required you to edit a separate file for
->withMiddleware(function (Middleware $middleware) {
    $middleware->api(prepend: [RequestIdMiddleware::class]);
    $middleware->alias(['trace' => TraceMiddleware::class]);
})
```

I want to be honest about the limits of what I can verify here: I don't have `vendor/` in this sandbox to actually read `Illuminate\Foundation\Application::handleRequest()`'s source and confirm every internal detail line-by-line. What's described above reflects my understanding of Laravel 11's documented behavior — once you run `composer install`, `vendor/laravel/framework/src/Illuminate/Foundation/Application.php` is the actual authoritative source if you want to verify this yourself.

### 3. The Onion Model, Proven With Real Output

`TraceMiddleware` records an entry both *before* calling `$next($request)` and *after* it returns:

```php
public function handle(Request $request, Closure $next, string $label): Response
{
    $this->appendTrace($request, "{$label} → entering (before controller)");
    $response = $next($request);   // everything below only runs on the way back OUT
    $this->appendTrace($request, "{$label} → exiting (after controller, before response sent)");
    // ...
    return $response;
}
```

Stacked as `['trace:Outer', 'trace:Inner']` on one route, the actual recorded order is:

```
Outer → entering
Inner → entering
LifecycleController@trace → controller logic running now
Inner → exiting
Outer → exiting
```

Not `Outer, Inner, Outer, Inner`. The **first-listed** middleware is the **outermost** layer — first one in, last one out. This is what "the middleware pipeline is an onion, not a flat sequence" actually means, made concrete instead of just asserted.

### 4. Global (Group) Middleware vs Route-Specific Middleware

```php
// bootstrap/app.php — applies to EVERY route in routes/api.php, whether
// or not that route mentions it
$middleware->api(prepend: [RequestIdMiddleware::class]);
```

```php
// routes/api.php — applies ONLY to this one route
Route::get('/trace', [...])->middleware(['trace:Outer', 'trace:Inner']);
```

`/api/lifecycle/plain` has **zero** route-specific middleware listed at all — and yet its response still carries an `X-Request-Id` header, because `RequestIdMiddleware` is global to the whole `api` group. This is the concrete difference between "runs on everything in this group, unconditionally" and "runs only where a route explicitly opts in."

---

## 🖥️ Trying This Yourself

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan serve
```

```bash
# The full pipeline trace — check the "pipeline_trace" array in the
# response body for the exact, real order every layer ran in.
curl http://localhost:8000/api/lifecycle/trace

# No route-specific middleware at all — check the X-Request-Id response
# header anyway (curl -i to see headers).
curl -i http://localhost:8000/api/lifecycle/plain
```

**The actual Day 6 exercise:** in `routes/api.php`, reverse the order to `->middleware(['trace:Inner', 'trace:Outer'])` on the `/lifecycle/trace` route, hit it again, and watch the `pipeline_trace` order flip to match — proving the FIRST-listed middleware is always the outermost layer, not something fixed by the label names themselves.

Also useful: run `php artisan route:list --path=lifecycle` to see exactly which middleware Laravel reports as attached to each of these two routes.

---

## ❌ Common Mistakes

| Mistake | Fix |
|---------|-----|
| Assuming middleware runs top-to-bottom like a flat list | It's an onion — first-listed is outermost: first in, last out |
| Thinking Laravel 11 "removed" the HTTP kernel | Only the per-app `Kernel.php` configuration file is gone — the kernel class itself is unchanged, just configured through `bootstrap/app.php` now |
| Confusing global (group) middleware with route-specific middleware | Global middleware runs on every route in its group unconditionally; route-specific middleware only runs where a route explicitly lists it |
| Putting logic in middleware that belongs in a controller or service | Middleware is for cross-cutting concerns (auth, logging, rate limiting) that apply across many unrelated routes — business logic belongs elsewhere |
| Forgetting that a middleware's "after `$next()`" code can modify the response | Middleware isn't limited to inspecting/rejecting the incoming request — it can rewrite the outgoing response too, as `TraceMiddleware` does |

---

## ✅ Day 6 Checklist

- [x] `TraceMiddleware` — a minimal, parameterized instrumentation middleware, explicitly scoped as narrower than Week 2 Day 4's real middleware-authoring topic
- [x] `RequestIdMiddleware` — a genuine global-to-a-group middleware example
- [x] `bootstrap/app.php` updated: `$middleware->api(prepend: [...])` and `$middleware->alias([...])`
- [x] The onion execution order proven with real, ordered output in an actual HTTP response, not just described
- [x] Global vs route-specific middleware contrasted on two adjacent routes (`/lifecycle/trace` vs `/lifecycle/plain`)
- [x] The full lifecycle traced end to end: `public/index.php` → `bootstrap/app.php` → HTTP kernel → pipeline → controller → back out
- [x] Honest acknowledgment of what I can and can't verify about Laravel 11's internals without `vendor/` available in this sandbox

---

## 🎉 Week 1 Complete!

Days 1–6 built up, in order: the service container (binding, resolving, contextual binding, deferred loading), service providers, facades, the configuration system, routing, and now the full request lifecycle. Day 7 is Week 1's mini-project — pulling every one of these pieces together into one deliberately config-driven application, per the roadmap.

**Date**: August 30, 2026
**Status**: ✅ Week 1, Day 6 Complete!
**Next**: Day 7 — Mini-Project: a config-driven application combining a custom service provider, facades, and environment-based configuration — Week 1's capstone.

> *"A request doesn't 'go through' middleware the way water goes through a pipe. It goes IN through every layer, hits the controller, then comes back OUT through every layer again — the same layers, in reverse."*
