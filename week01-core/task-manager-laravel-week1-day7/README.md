# Laravel Roadmap — Week 1, Day 7: Mini-Project

[![Status](https://img.shields.io/badge/Status-Completed-brightgreen.svg)]()
[![Day](https://img.shields.io/badge/Day-7-blue.svg)]()
[![Topic](https://img.shields.io/badge/Topic-Week%201%20Capstone-orange.svg)]()

> **"Six days of separate lessons. One endpoint that needs every single one of them to actually work."**

---

## ⚠️ Same Setup Note as Days 1–6

Hand-built Laravel 11 skeleton, no `vendor/` directory (no Packagist access in this sandbox). Run this first:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

---

## 🎯 What This Mini-Project Is

The direct Laravel equivalent of the Spring Boot journey's Week 1 capstone — a **config-driven greeting service**: three interchangeable strategies (`Formal`, `Casual`, `Festive`), the active one picked entirely by configuration, with an environment-aware behavior layered on top. Per the roadmap's own rule ("mini-project each week — combines everything from that week"), this single feature deliberately exercises all six days:

| Day | What it contributes to this mini-project |
|---|---|
| 1–2 | Automatic constructor resolution of `GreetingServiceInterface` — no manual container calls anywhere in the controller |
| 3 | A **dedicated service provider** (`GreetingStrategyServiceProvider`) owns the binding — not a bare `bind()` scattered elsewhere |
| 4 | **Which** strategy is active is driven entirely by `config('greeting.active_strategy')`, sourced from `.env` |
| 5 | Reached via a properly named route (`mini-project.greet`) |
| 6 | Routed through `TraceMiddleware` — the response includes a real pipeline trace |

---

## 💡 What's New Today

### 1. A Third Way to Pick an Implementation

Days 1–3 established two ways to resolve `GreetingServiceInterface`: a single hardcoded global `bind()`, and *contextual* bindings keyed to which class is asking. Today adds a third: **config-driven selection**.

```php
// GreetingStrategyServiceProvider::register()
$this->app->bind(GreetingServiceInterface::class, function () {
    $strategy = config('greeting.active_strategy');

    return match ($strategy) {
        'formal' => new FormalGreetingService,
        'casual' => new CasualGreetingService,
        'festive' => new FestiveGreetingService,
        default => throw new InvalidArgumentException(/* ... */),
    };
});
```

Change `GREETING_ACTIVE_STRATEGY` in `.env` from `casual` to `festive`, and every consumer relying on the plain global default gets a different concrete class — zero PHP source changes, zero redeploys of application code.

### 2. This Genuinely Superseded Old Code — Not Just Added To It

Building this properly meant going back and **removing** the old hardcoded global `bind(GreetingServiceInterface::class, CasualGreetingService::class)` that used to live in `AppServiceProvider` since Day 2. `GreetingStrategyServiceProvider` is now the single place responsible for that global default. The two *contextual* bindings `AppServiceProvider` still defines (for `GreetingController` and `CasualGreetingController` specifically) are completely unaffected — Laravel's container tracks contextual rules separately from the plain global default, so nothing about Day 2/3's demonstrated behavior for those two controllers changed at all.

**Why this matters as a lesson in its own right:** a real capstone isn't just gluing six unrelated demos together — it's revisiting earlier decisions once a better pattern is available, and understanding precisely what does and doesn't need to change when you do.

### 3. Keeping Day 3's Demonstrated Contrast Alive, on Purpose

`FacadeDemoController` (Day 3) relies on the facade's resolved class genuinely differing from `GreetingController`'s contextual override, to prove contextual bindings don't apply to bare static calls. Defaulting `GREETING_ACTIVE_STRATEGY` to `casual` in `.env.example` was a deliberate choice — it preserves that exact contrast (`GreetingController` → `Formal` via context; the facade → `Casual` via the new config-driven default) while genuinely upgrading the *mechanism* underneath from hardcoded to config-driven. Change the env var to `formal` yourself and that contrast disappears — not because contextual binding started applying to the facade, but because the config-driven default happens to coincide with the contextual override's result. `FacadeDemoController`'s response text was updated to explain this precisely, rather than left stale and wrong.

### 4. Environment-Based Behavior, Layered on Top

```php
// GreetingStrategyController::greet()
if (config('app.env') !== 'production') {
    $message .= ' [Running in '.config('app.env').' — this suffix is suppressed in production]';
}
```

This mirrors the Spring Boot journey's "profile-based default message (dev vs prod)" exercise directly: the *strategy* is config-driven (section 1), and *separately*, this controller layers one additional environment-aware behavior on top — reading `config('app.env')`, never `env()` directly, consistent with Day 4's rule about where `env()` is and isn't safe to call.

### 5. One Endpoint, Six Days

```bash
curl http://localhost:8000/api/mini-project/greet/Sam
```

```json
{
  "message": "Hey Sam! Ready to get some tasks done? [Running in local — this suffix is suppressed in production]",
  "active_strategy": "casual",
  "resolved_implementation": "App\\Services\\CasualGreetingService",
  "environment": "local",
  "pipeline_trace": [
    "MiniProject → entering (before controller)",
    "GreetingStrategyController@greet → ...",
    "MiniProject → exiting (after controller, before response sent)"
  ]
}
```

Every field in that response depends on a different day: `resolved_implementation` and `active_strategy` on Days 3–4's config-driven provider; `message`'s environment suffix on Day 4's `config()` discipline; `pipeline_trace` on Day 6's middleware; the route itself existing at all on Day 5's naming/grouping conventions; and the whole thing resolving automatically in the first place on Days 1–2's container work.

---

## 🖥️ Trying This Yourself

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan serve
```

```bash
curl http://localhost:8000/api/mini-project/greet/Sam
```

**The actual Day 7 exercise:**
1. Change `GREETING_ACTIVE_STRATEGY` in `.env` to `festive`, hit the endpoint again — `resolved_implementation` changes to `FestiveGreetingService`, with zero code touched.
2. Change it to something invalid, like `sarcastic` — the app should fail loudly with an `InvalidArgumentException` at the point of resolution, not silently misbehave.
3. Compare `GET /api/demo/greet/Sam` (`GreetingController`, contextual → always `Formal`) against `GET /api/demo/greet-facade/Sam` (the facade, config-driven default) — with `GREETING_ACTIVE_STRATEGY=casual`, these differ; set it to `formal` and they coincide, for the reason explained in section 3 above.
4. Change `APP_ENV` in `.env` to `production` and confirm the environment suffix disappears from the mini-project endpoint's message.

---

## ❌ Common Mistakes

| Mistake | Fix |
|---------|-----|
| Leaving two competing bindings for the same interface in different providers | Whichever provider's `register()` runs last silently wins — remove the superseded one entirely, as done here |
| Silently defaulting to a fallback strategy on an invalid config value | Fail loudly instead (`InvalidArgumentException`) — a typo in `.env` should be obvious immediately, not a confusing mystery later |
| Forgetting a mini-project's job is integration, not new isolated demos | Every piece here reuses infrastructure from an earlier day — the facade, the trace middleware, the config helper discipline — rather than reinventing anything |
| Leaving stale explanatory text after changing underlying behavior | `FacadeDemoController`'s response text was updated to match the new config-driven mechanism, not left describing code that no longer exists |

---

## ✅ Day 7 (Week 1 Capstone) Checklist

- [x] Three interchangeable `GreetingServiceInterface` strategies (`Formal`, `Casual`, `Festive`)
- [x] A dedicated service provider (`GreetingStrategyServiceProvider`) owning config-driven selection
- [x] The old, now-superseded hardcoded global binding actually removed from `AppServiceProvider`, not left dangling
- [x] Fail-fast validation on an invalid config value
- [x] Environment-based behavior layered on top, using `config()` discipline from Day 4
- [x] Reached via a named route, routed through Day 6's trace middleware
- [x] Day 3's facade-vs-contextual-binding contrast re-verified and its explanation corrected to match the new mechanism

---

## 🎉 Week 1 Complete!

Six days, one working mini-project that couldn't function without any single one of them. Every piece of infrastructure Week 1 built — the container, providers, facades, config, routing, middleware — is now load-bearing, not just demonstrated in isolation.

**Date**: August 30, 2026
**Status**: ✅ Week 1 Complete!
**Next**: Week 2 — The HTTP Layer: resource controllers, form request validation, JSON responses and API resources, real custom middleware (the deeper treatment this week's `TraceMiddleware`/`RequestIdMiddleware` deliberately didn't attempt), and Week 2's own mini-project — a full RESTful Task API with proper HTTP verbs, status codes, and a Postman collection.

> *"A mini-project isn't a bigger demo. It's the moment every earlier day's shortcut — 'we'll build on this properly later' — actually gets called in."*
