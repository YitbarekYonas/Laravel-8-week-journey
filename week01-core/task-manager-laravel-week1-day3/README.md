# Laravel Roadmap — Week 1, Day 3

[![Status](https://img.shields.io/badge/Status-Completed-brightgreen.svg)]()
[![Day](https://img.shields.io/badge/Day-3-blue.svg)]()
[![Topic](https://img.shields.io/badge/Topic-Providers%2C%20Deferred%20Loading%2C%20Facades-orange.svg)]()

> **"A facade isn't a static class in disguise. It's a container resolution wearing a static call as a costume."**

---

## ⚠️ Same Setup Note as Days 1–2

Hand-built Laravel 11 skeleton, no `vendor/` directory (no Packagist access in this sandbox). Run this first:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

---

## 🎯 Learning Objectives

- ✅ See a concrete case where `register()`-time resolution is fragile, and `boot()`-time resolution is safe — not just told the rule
- ✅ Build and observe a real `DeferrableProvider` — watch it go from "never loaded" to "loaded" over HTTP
- ✅ Understand exactly what happens when you call a static method on a facade — the real mechanism, not the metaphor
- ✅ See contextual binding's actual boundary: it applies to constructor dependencies of a *named class*, not to a facade's bare static call

---

## 💡 What I Learned Today

### 1. `register()` vs `boot()` — Why the Rule Exists, Proven

The rule everyone repeats is "only bind things in `register()`; do real work in `boot()`." Here's *why*, made concrete: `StartupBannerServiceProvider` wants to resolve `GreetingServiceInterface` — a binding `AppServiceProvider` is responsible for — to log a startup message.

```php
public function boot(): void
{
    $greetingService = $this->app->make(GreetingServiceInterface::class);
    // ... safe, guaranteed to work
}
```

If this same line ran inside `StartupBannerServiceProvider::register()` instead, it would currently *happen* to work — `bootstrap/providers.php` lists `AppServiceProvider` first, so its binding exists by the time `StartupBannerServiceProvider::register()` runs. But that correctness is an **accident of list order**, not a guarantee. Reorder those two lines, or add a third provider in between that also needs to register bindings first, and the exact same code could resolve `GreetingServiceInterface` before it exists.

`register()` runs for every provider, strictly in listed order, before *any* provider's `boot()` runs. `boot()` runs only after *every* provider's `register()` has completed — for the whole app, regardless of list order. That's the actual guarantee, and it's why resolving another provider's binding belongs in `boot()`, never in `register()`.

**Check `storage/logs/laravel.log`** after any request — `StartupBannerServiceProvider::boot()` logs its successful resolution on every request, as living proof this isn't just a comment claiming it works.

### 2. Deferred Providers — Proven Over HTTP, Not Just Asserted

`ReportServiceProvider` implements `DeferrableProvider`:

```php
class ReportServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        file_put_contents(storage_path('logs/deferred-provider-proof.log'), /* ... */);
        $this->app->singleton(ReportGeneratorInterface::class, ReportGeneratorService::class);
    }

    public function provides(): array
    {
        return [ReportGeneratorInterface::class];
    }
}
```

Every other provider in this app has `register()` called unconditionally, on every request. `DeferrableProvider` changes that: Laravel reads `provides()` to learn *which binding* this provider is responsible for, **without calling `register()` yet**, and only actually calls it the first time something resolves `ReportGeneratorInterface`.

This project makes that genuinely observable, not just theoretical — `register()` writes a proof file the instant it actually runs:

```bash
curl http://localhost:8000/api/deferred/status    # {"deferred_provider_has_run": false}
curl http://localhost:8000/api/deferred/generate   # triggers register() for the first time
curl http://localhost:8000/api/deferred/status    # {"deferred_provider_has_run": true}
```

`/api/deferred/status` never touches `ReportGeneratorInterface` at all — it only checks whether the proof file exists. Hitting it repeatedly *before* calling `/generate` should never flip to `true`, because nothing has asked the container for that binding yet.

**Why this matters beyond a demo:** `ReportGeneratorService` stands in for something genuinely expensive (a PDF renderer, a heavy third-party client). Most requests to a real app never touch reporting — constructing that dependency on *every* request regardless would be pure waste. Deferred loading means that cost is paid only by the requests that actually need it.

### 3. Facades — What `Greeting::greet('Sam')` Actually Does

`Greeting` (in `app/Facades/Greeting.php`) doesn't define a `greet()` method at all:

```php
class Greeting extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return GreetingServiceInterface::class;
    }
}
```

That's the *entire* class. So what actually happens when you call `Greeting::greet('Sam')`?

1. PHP looks for a real static `greet()` method, doesn't find one, and falls through to `__callStatic('greet', ['Sam'])` — inherited from the real parent class, `Illuminate\Support\Facades\Facade`.
2. That inherited method calls `static::getFacadeRoot()`, which reads `getFacadeAccessor()` to learn the binding key (`GreetingServiceInterface::class`), and asks the container to resolve it — **exactly** the same operation as `app(GreetingServiceInterface::class)`.
3. It forwards the original call — `greet('Sam')` — onto that resolved object, and returns whatever it returns.

`FacadeDemoController` calls `Greeting::getFacadeRoot()` directly, to make step 2 externally visible instead of something you just have to trust:

```php
$resolvedInstance = Greeting::getFacadeRoot();
// get_class($resolvedInstance) reveals exactly what got resolved
```

**A facade is not "a static version of a service."** It's a thin proxy that does a completely ordinary container resolution and method call — the `::` syntax is purely cosmetic.

### 4. The Real Boundary of Contextual Binding

This is the part that trips people up, so this project makes it directly comparable: `AppServiceProvider`'s global fallback binding resolves `GreetingServiceInterface` to `CasualGreetingService`. `GreetingController` has its *own* contextual override to `FormalGreetingService`.

```bash
curl http://localhost:8000/api/greet/Sam         # GreetingController → FormalGreetingService (contextual)
curl http://localhost:8000/api/greet-facade/Sam   # Greeting facade   → CasualGreetingService (global fallback)
```

Same interface, genuinely different resolved classes. **Why doesn't the facade get `GreetingController`'s override?** Because contextual binding (`->when(SomeClass::class)->needs(...)->give(...)`) only applies when the container is resolving a *constructor dependency of that specific, named class*. A facade's static call has no "consuming class" at all — there's no constructor being built, so there's nothing for a `->when()` rule to match against. It falls straight through to the plain global `bind()`.

---

## 🖥️ Trying This Yourself

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan serve
```

```bash
# 1. register() vs boot() — check storage/logs/laravel.log after this
curl http://localhost:8000/

# 2. Deferred provider — watch it flip from false to true
curl http://localhost:8000/api/deferred/status
curl http://localhost:8000/api/deferred/generate
curl http://localhost:8000/api/deferred/status
curl http://localhost:8000/api/deferred/reset      # resets the demo

# 3. Facade internals vs contextual binding's real boundary
curl http://localhost:8000/api/greet/Sam           # FormalGreetingService
curl http://localhost:8000/api/greet-facade/Sam    # CasualGreetingService — genuinely different
```

---

## ❌ Common Mistakes

| Mistake | Fix |
|---------|-----|
| Resolving another provider's binding inside `register()` | Only safe by accident of list order — move it to `boot()`, where the guarantee is real |
| Doing real bootstrapping work (queries, I/O, heavy setup) in `register()` | `register()` should only bind — `boot()` is where actual work belongs |
| Forgetting to implement `provides()` on a `DeferrableProvider` | Without it, Laravel doesn't know which bindings the provider is responsible for, and can't defer it correctly |
| Assuming a facade is somehow different from `app()->make()` under the hood | It's exactly the same container resolution, forwarded through `__callStatic` — verified here via `getFacadeRoot()` |
| Expecting a facade call to respect a contextual binding tied to some other class | Contextual bindings only apply to a *named class's* constructor dependencies — a facade's static call has no such context |

---

## ✅ Day 3 Checklist

- [x] `StartupBannerServiceProvider` — resolves another provider's binding safely in `boot()`, with the fragility of doing the same in `register()` explained in comments
- [x] `ReportServiceProvider implements DeferrableProvider` — a real, working deferred provider with `provides()`
- [x] Deferred loading proven externally observable over HTTP (`/api/deferred/status` → `/generate` → `/status` again)
- [x] A real custom facade (`Greeting`) with only `getFacadeAccessor()` implemented
- [x] `getFacadeRoot()` called directly to reveal the resolved instance, not just trusted
- [x] The global-fallback-vs-contextual-binding contrast made genuinely observable (two different resolved classes, side by side)

---

**Date**: August 27, 2026
**Status**: ✅ Week 1, Day 3 Complete!
**Next**: Day 4 — Configuration System: config files, `.env`, environment-specific config, and config caching (`php artisan config:cache`) — including what actually stops working once config is cached.

> *"Every 'magic' feature in Laravel — facades, auto-resolution, deferred providers — turns out to be a small, readable mechanism once you look directly at it instead of at the metaphor built around it."*
