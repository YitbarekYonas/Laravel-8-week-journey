# Laravel Roadmap — Week 1, Day 2

[![Status](https://img.shields.io/badge/Status-Completed-brightgreen.svg)]()
[![Day](https://img.shields.io/badge/Day-2-blue.svg)]()
[![Topic](https://img.shields.io/badge/Topic-Service%20Container%20Deep%20Dive-orange.svg)]()

> **"bind() and singleton() answer the same question — 'what do I hand back?' — with two different lifetimes. Getting that choice wrong is the most common way a service container bites you."**

---

## ⚠️ Same Setup Note as Day 1

This is still a hand-built Laravel 11 skeleton — no `vendor/` directory (no Packagist access in this sandbox). Run this first:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

---

## 🎯 Learning Objectives

- ✅ See `bind()` vs `singleton()` produce genuinely different, observable behavior — not just read about the difference
- ✅ Understand contextual binding: the same interface, resolved differently depending on which class is asking
- ✅ Use `app()->make()` to resolve dependencies manually, on demand, from both a controller and a console command
- ✅ Resolve a class with a constructor parameter the container can't infer on its own, by supplying it explicitly

---

## 💡 What I Learned Today

### 1. `bind()` vs `singleton()` — Proven, Not Just Explained

Day 1's `GreetingServiceInterface` implementations were stateless — every call just computed a string from its input. That was a poor example for this distinction, because a fresh instance and a reused instance behave *identically* when there's no state to lose.

`SequentialIdGenerator` fixes that — it holds a counter:

```php
class SequentialIdGenerator implements IdGeneratorInterface
{
    private int $counter = 0;

    public function next(): string
    {
        $this->counter++;
        return sprintf('id-%d (instance #%d)', $this->counter, spl_object_id($this));
    }
}
```

Bound as a **singleton**:
```php
$this->app->singleton(IdGeneratorInterface::class, SequentialIdGenerator::class);
```
Every resolution — even from a totally different part of the app, even across multiple `app()->make()` calls in the same request — returns the **same object**. `IdDemoController` calls it three times in one method; hit `/api/ids/demo` and you'll see `id-1`, `id-2`, `id-3`, with the identical `instance #` on every line.

Switch that line to `bind()` instead (the commented-out alternative sitting right below it in `AppServiceProvider`), and every single resolution constructs a **brand-new** `SequentialIdGenerator` — a fresh object, with its counter back at zero. All three lines in the response become `id-1`, with three different `instance #` values.

**The actual lesson:** `bind()` is the safe default for anything stateless or where shared state would be a bug (imagine two unrelated requests accidentally sharing a counter). `singleton()` is for things that are either expensive to construct (so you want exactly one, reused) or genuinely need shared state across a request's lifetime — a connection, a cache client, a per-request context.

### 2. Contextual Binding — "It Depends Who's Asking"

`GreetingController` and the new `CasualGreetingController` both type-hint the exact same `GreetingServiceInterface`. A single global `bind()` has no way to distinguish between them — every consumer of that interface gets whatever the one global rule says.

```php
$this->app->when(GreetingController::class)
    ->needs(GreetingServiceInterface::class)
    ->give(FormalGreetingService::class);

$this->app->when(CasualGreetingController::class)
    ->needs(GreetingServiceInterface::class)
    ->give(CasualGreetingService::class);
```

Now the container answers differently **depending on which class is doing the asking**. Hit both routes — same interface dependency in both controllers' constructors, genuinely different concrete classes resolved:

```bash
curl http://localhost:8000/api/greet/Sam        # FormalGreetingService
curl http://localhost:8000/api/greet-casual/Sam # CasualGreetingService
```

This is the real justification for contextual binding — it's not a toy feature. Two different consumers with genuinely different needs, sharing one contract, is an entirely ordinary situation once an app has more than a couple of controllers.

**Precedence, made visible on purpose:** `AppServiceProvider` still has the old Day 1 global `bind()` sitting below both contextual rules. It's now genuinely dead code *for these two controllers specifically* — their contextual rules take precedence — but it's left in deliberately so you can see that a global binding and contextual bindings can coexist, with the more specific (contextual) rule winning whenever both could apply to the same resolution.

### 3. Manual Resolution — `app()->make()`

Every example through Day 1 relied on **automatic** resolution: type-hint a constructor parameter, let the container fill it in when the class is instantiated. That only fires once — when the class itself is built. To resolve something on-demand, mid-method, potentially more than once, you ask the container directly:

```php
$first  = app(IdGeneratorInterface::class)->next();
$second = app(IdGeneratorInterface::class)->next();
$third  = app()->make(IdGeneratorInterface::class)->next();
```

`app('Some\Interface')` and `app()->make('Some\Interface')` are exactly equivalent — `app()` with zero arguments just returns the container itself; `app('X')` is shorthand for `app()->make('X')`. `IdDemoController::demonstrate()` calls all three, deliberately mixing both syntaxes, to resolve the exact same binding three separate times within one request — which is the only way to actually *observe* whether singleton reuse is happening, rather than take it on faith.

### 4. Resolving with Explicit Parameters

`WelcomeBanner` takes a plain `string $appName` constructor argument — not a type-hinted class the container can resolve on its own. Automatic (and plain manual) resolution has nothing to offer here; the container has no way to guess what string you want.

```php
$banner = app()->make(WelcomeBanner::class, [
    'appName' => config('app.name'),
]);
```

The second argument to `make()` supplies exactly the parameters the container can't infer by itself. Any *other* constructor parameters that ARE type-hinted classes would still get auto-resolved normally — this only overrides what you explicitly name, it doesn't replace automatic resolution for everything else in the constructor.

---

## 🖥️ Trying This Yourself

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan serve
```

```bash
# Contextual binding — same interface, different resolved class
curl http://localhost:8000/api/greet/Sam
curl http://localhost:8000/api/greet-casual/Sam

# singleton() in action — three manual resolutions, one shared instance
curl http://localhost:8000/api/ids/demo
```

And from the console — the exact same container, a completely different entry point:
```bash
php artisan container:demo
```

**The actual Day 2 exercise:** in `AppServiceProvider`, swap the `IdGeneratorInterface` binding from `singleton()` to the commented-out `bind()` alternative. Hit `/api/ids/demo` again (no restart needed) and watch every `instance #` become different and every counter reset to `id-1`. Then swap it back and confirm the singleton behavior returns.

---

## ❌ Common Mistakes

| Mistake | Fix |
|---------|-----|
| Using `singleton()` by default "to be safe" | The opposite is usually true — shared state across unrelated requests is a subtle, hard-to-reproduce bug. Default to `bind()`; reach for `singleton()` deliberately |
| Assuming two classes with the same interface dependency must resolve the same way | That's only true until you need contextual binding — a completely normal, common situation |
| Forgetting contextual bindings take precedence over a global `bind()` | Both can coexist; the more specific rule wins whenever it applies |
| Trying to auto-resolve a class with plain scalar constructor parameters | The container can't guess a string/int value — use `app()->make(Class::class, ['param' => $value])` instead |
| Confusing `app('X')` and `app()->make('X')` as somehow different | They're exactly equivalent — the first is shorthand for the second |

---

## ✅ Day 2 Checklist

- [x] `SequentialIdGenerator` — a stateful implementation, specifically chosen to make `bind()` vs `singleton()` observable
- [x] `singleton()` proven via three manual resolutions in one request, all returning the same object and an incrementing counter
- [x] The `bind()` alternative left commented-out in place, ready to swap and compare directly
- [x] Contextual binding — two controllers, one shared interface dependency, two different resolved classes based on `->when()->needs()->give()`
- [x] Manual resolution via both `app('X')` and `app()->make('X')` syntax
- [x] A class resolved with explicit constructor parameters the container couldn't infer on its own (`WelcomeBanner`)
- [x] The exact same container exercised from two different entry points — an HTTP controller and an artisan command

---

**Date**: August 24, 2026
**Status**: ✅ Week 1, Day 2 Complete!
**Next**: Day 3 — Service Providers: `register()` vs `boot()` in real depth, deferred providers, and what a facade actually is under the hood.

> *"Automatic resolution is what makes the container feel invisible. Manual resolution is what proves it was never actually magic — just a lookup table you can inspect and call directly whenever you want to."*
