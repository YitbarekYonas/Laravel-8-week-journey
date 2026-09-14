# Laravel Roadmap — Week 2, Day 4

[![Status](https://img.shields.io/badge/Status-Completed-brightgreen.svg)]()
[![Day](https://img.shields.io/badge/Day-4-blue.svg)]()
[![Topic](https://img.shields.io/badge/Topic-Middleware-orange.svg)]()

> **"Week 1 Day 6 proved the pipeline exists. Today's middleware actually uses it for something a request could fail."**

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

- ✅ Write middleware with real, meaningful branching logic — not pure instrumentation
- ✅ Understand terminable middleware: the `terminate()` hook, when it runs, and why that timing matters
- ✅ Define a genuinely new, custom middleware group — not just modify Laravel's built-in `web`/`api` groups
- ✅ Understand precisely what today's `ApiKeyMiddleware` is and isn't (a shared-secret gate, not real authentication)

---

## 💡 What I Learned Today

### 1. The Difference Between Instrumentation and Real Middleware

Week 1 Day 6 was explicit about this: `TraceMiddleware` and `RequestIdMiddleware` existed purely to make the pipeline *observable*, deliberately narrower than what real middleware-authoring looks like. `ApiKeyMiddleware` is that real treatment:

```php
public function handle(Request $request, Closure $next): Response
{
    $providedKey = $request->header('X-Api-Key');
    $expectedKey = config('security.api_key');

    if ($providedKey !== $expectedKey) {
        return response()->error('Missing or invalid X-Api-Key header.', 401);
    }

    return $next($request);
}
```

The distinguishing feature: this middleware can **reject** a request outright, before a controller ever sees it, based on genuine application logic — not just record that a request passed through.

**Honest scope note, stated directly:** this checks one shared secret against one configured value. It answers "does this caller know the secret?" — never "who is this caller?" Real per-user authentication (Laravel Sanctum, tokens tied to individual users, login/logout) is Week 5's topic. Calling this "authentication" would overstate what it does.

### 2. Terminable Middleware — Work That Happens After the Client Already Has Their Response

```php
class ResponseTimeLoggerMiddleware
{
    private float $startedAt;

    public function handle(Request $request, Closure $next): Response
    {
        $this->startedAt = microtime(true);
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        $durationMs = round((microtime(true) - $this->startedAt) * 1000, 2);
        Log::info('Request completed', [/* ... */]);
    }
}
```

A middleware class becomes "terminable" just by having a `terminate()` method — no interface to implement, no special registration step. Laravel detects it automatically and calls `terminate()` **on this same instance**, after the response has already been sent to the client. The `$this->startedAt` property set in `handle()` is still readable in `terminate()` precisely because it's the same object — not a fresh instance.

**Why the timing matters:** logging, analytics, cleanup — anything that doesn't need to happen before the client gets their answer — belongs in `terminate()` specifically because doing it there adds **zero** perceived latency to the request. If this logging happened at the end of `handle()` instead, the client would wait for the log write to complete before receiving their response.

### 3. A Genuinely New, Custom Middleware Group

```php
// bootstrap/app.php
$middleware->group('secure-api', [
    ApiKeyMiddleware::class,
    ResponseTimeLoggerMiddleware::class,
]);
```

Week 1 Day 6 only ever *modified* Laravel's own built-in `api` group (`$middleware->api(prepend: [...])`). `->group()` here defines something that doesn't exist anywhere in the framework — `secure-api` is entirely this project's own bundle. Any route listing `->middleware('secure-api')` gets both middleware applied together, in this exact order, without repeating either one individually — the same principle behind Laravel's own `web`/`api` groups, just applied to a bundle you define yourself.

```php
Route::middleware('secure-api')->prefix('secure')->name('secure.')->group(function () {
    Route::get('/ping', function () { /* ... */ })->name('ping');
});
```

---

## 🖥️ Trying This Yourself

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan serve
```

```bash
# No key at all — rejected by ApiKeyMiddleware before the route closure runs
curl -i http://localhost:8000/api/secure/ping

# Wrong key — same result
curl -i -H "X-Api-Key: wrong-key" http://localhost:8000/api/secure/ping

# The correct key (matches .env's DEMO_API_KEY default) — succeeds
curl -i -H "X-Api-Key: demo-secret-key" http://localhost:8000/api/secure/ping
```

**The actual Day 4 exercise:** after a successful request to `/api/secure/ping`, check `storage/logs/laravel.log` — you should find a `"Request completed"` entry with a real `duration_ms` value, written by `ResponseTimeLoggerMiddleware::terminate()`, confirming it ran after your `curl` command had already received its response.

---

## ❌ Common Mistakes

| Mistake | Fix |
|---------|-----|
| Calling a shared-secret check "authentication" | It answers "does this caller know a secret," not "who is this caller" — a meaningfully different, weaker guarantee |
| Doing logging/analytics work at the end of `handle()` instead of in `terminate()` | That work then delays the client's response for no reason — `terminate()` exists specifically to avoid this |
| Assuming `terminate()` needs its own interface or registration | Any middleware with a `terminate()` method is automatically treated as terminable — nothing else required |
| Repeating the same list of middleware on every route that needs them | Define a custom group once (`$middleware->group(...)`), then reference it by name everywhere |
| Confusing modifying a built-in group (`$middleware->api(...)`) with defining a new one (`$middleware->group(...)`) | They're different operations — one changes an existing group's contents, the other creates a brand new named group |

---

## ✅ Day 4 Checklist

- [x] `ApiKeyMiddleware` — real branching logic that can genuinely reject a request, with an honest scope note distinguishing it from real authentication
- [x] `ResponseTimeLoggerMiddleware` — a real terminable middleware, `terminate()` proven to run after the response is sent via a real log entry
- [x] `config/security.php` — the shared secret sourced via `env()`, only inside a config file (Week 1 Day 4's rule, still followed)
- [x] A genuinely new custom middleware group (`secure-api`), distinct from modifying Laravel's built-in `web`/`api` groups
- [x] The Week 2 Day 3 `error()`/`success()` macros reused here, proving they're app-wide conventions, not controller-specific helpers

---

**Date**: September 12, 2026
**Status**: ✅ Week 2, Day 4 Complete!
**Next**: Day 5 — API Resources: `JsonResource`, `ResourceCollection`, conditional attributes, and pagination — finally giving `Task::toArray()` a proper, centralized replacement.

> *"Instrumentation tells you a request passed through. Real middleware decides whether it's allowed to."*
