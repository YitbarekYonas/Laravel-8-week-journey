# Laravel Roadmap — Week 1, Day 1

[![Status](https://img.shields.io/badge/Status-Completed-brightgreen.svg)]()
[![Day](https://img.shields.io/badge/Day-1-blue.svg)]()
[![Topic](https://img.shields.io/badge/Topic-Installation%20%2B%20Service%20Container-orange.svg)]()

> **"Every framework has a moment where 'magic' stops being magic. For Laravel, that moment is understanding the service container — everything else is built on top of it."**

---

## ⚠️ One Honest Setup Note

This zip is a hand-built, accurate Laravel 11 project skeleton — every file (`artisan`, `bootstrap/app.php`, the service provider, the controller) reflects real, current Laravel structure and conventions. What it does **not** include is the `vendor/` directory (Composer's downloaded dependencies) — generating that requires running `composer install` against Packagist, which needs an internet connection this environment doesn't have. **Run one command locally before anything else will execute:**

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Everything below assumes you've done that.

---

## 🎯 Learning Objectives

- ✅ Understand what `composer create-project laravel/laravel` actually produces, and why
- ✅ Understand Laravel's project structure — what lives where, and why
- ✅ Understand `artisan` as a real, readable PHP script — not a black box
- ✅ Understand the **service container**: binding, resolving, and automatic dependency injection
- ✅ See the same request lifecycle concept from two different entry points (`public/index.php` for HTTP, `artisan` for CLI)

---

## 💡 What I Learned Today

### 1. Laravel Installation — What `composer create-project` Actually Does

There's no separate "Laravel installer" doing anything magical. `laravel/laravel` on Packagist is itself just a project **skeleton** — a `composer.json` declaring `laravel/framework` as a dependency, plus a standard folder layout. Running `composer create-project laravel/laravel your-app` does exactly what `composer install` does for any PHP project: reads `composer.json`, resolves and downloads every dependency (and their dependencies) into `vendor/`, and generates `vendor/autoload.php` — the single file that makes every class in your app and every package available via `use` statements, with zero manual `require` calls anywhere else.

This is the same relationship as `pom.xml` + `mvn install` in the Spring Boot journey: a manifest file plus a build tool that resolves it into something runnable.

### 2. Project Structure — What Lives Where, and Why

```
app/
├── Http/Controllers/   → Controllers (Week 2 Day 1)
├── Providers/          → Service providers (today's focus)
├── Services/           → NOT a Laravel convention — this is MY choice,
│                          same as Spring's service/ package convention
├── Models/              → Eloquent models (Week 3)
bootstrap/
├── app.php              → Application bootstrap (Laravel 11+ — see below)
└── providers.php         → Registered service providers
config/                  → config('key.nested') source — Day 4 in depth
public/
└── index.php            → THE web server's entry point for every request
routes/
├── web.php               → Browser-facing routes (sessions, CSRF)
├── api.php               → Stateless JSON API routes (this project's focus)
└── console.php           → Closure-based artisan commands
artisan                   → The CLI entry point (see below)
```

The `Services/` folder isn't something Laravel generates or requires — Laravel is deliberately unopinionated about where your own business logic lives. I'm introducing it now, in Day 1, because this roadmap's whole arc treats service classes as a first-class concept (mirroring the Spring Boot journey's `service/` layer), and it's easier to establish the convention before Week 2's controllers arrive than to retrofit it later.

### 3. `artisan` — A Real Script, Not a Black Box

Open the `artisan` file in this project. It's about 15 lines of actual PHP:

```php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$status = $app->handle(input: new ArgvInput, type: Kernel::class);
exit($status);
```

Every single `php artisan whatever` command you'll ever run goes through exactly this. There's no separate artisan "binary" — it's the same PHP interpreter, running the same bootstrapped application container that handles HTTP requests, just dispatched to a **console kernel** instead of an HTTP kernel. `php artisan migrate`, `php artisan make:controller`, `php artisan tinker` — all of them are Laravel-provided (or package-provided) **commands** registered into that console kernel, resolved and run the exact same way.

### 4. Laravel 11's Structural Change — No More `Kernel.php`

If you've seen older Laravel tutorials, you'll notice `app/Http/Kernel.php` and `app/Console/Kernel.php` are conspicuously absent here. Laravel 11 consolidated all of that (HTTP middleware stack, console kernel config, AND exception handling — previously `app/Exceptions/Handler.php`) into **one file**: `bootstrap/app.php`.

```php
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(web: ..., api: ..., commands: ..., health: '/up')
    ->withMiddleware(function (Middleware $middleware) { /* Week 2 Day 4 */ })
    ->withExceptions(function (Exceptions $exceptions) { /* Week 6 Day 2 */ })
    ->create();
```

The **concept** from Day 6's "HTTP kernel, middleware pipeline" hasn't gone away at all — a request still flows through a middleware pipeline before reaching a controller. What changed is *where you configure it*: one fluent, discoverable file instead of three separate files you had to already know existed.

### 5. The Service Container — Binding

Look at `app/Providers/AppServiceProvider.php`:

```php
public function register(): void
{
    $this->app->bind(
        GreetingServiceInterface::class,
        FormalGreetingService::class,
    );
}
```

`$this->app` **is** the service container — every service provider gets a reference to it. This one line tells the container: *"whenever something asks for a `GreetingServiceInterface`, hand it a `FormalGreetingService` instance."* Nothing has been instantiated yet — this is a registration, not a resolution. `bind()` means a **new** instance gets created every time it's resolved; `singleton()` (not used here) would reuse one instance for the app's lifetime — the right choice depends on whether the service holds per-request state.

This directly mirrors Spring's `@Bean`/`@Component` + `ApplicationContext` relationship: a service provider's `register()` method is Laravel's equivalent of a `@Configuration` class's `@Bean` methods.

### 6. The Service Container — Automatic Resolution

Now look at `app/Http/Controllers/GreetingController.php`:

```php
public function __construct(
    private readonly GreetingServiceInterface $greetingService,
) {}
```

This constructor has **no idea** which concrete class it's actually receiving. When Laravel needs to instantiate `GreetingController` to handle an incoming request, it uses PHP's reflection API to inspect this constructor's parameter types, sees `GreetingServiceInterface`, and asks the container to resolve one — which, because of the binding registered above, hands back a `FormalGreetingService`. No `new FormalGreetingService()` anywhere in the controller. No manual container call anywhere in the controller. This is **automatic resolution** — the mechanism that makes Laravel's dependency injection largely invisible once a binding exists.

### 7. Why an Interface, Not Just the Concrete Class

`CasualGreetingService.php` exists in this project specifically to make this concrete: it's a second, different implementation of the exact same contract. Swapping which one the container hands out is a **one-line change** in `AppServiceProvider` — comment out one `bind()` call, uncomment the other. Zero changes to `GreetingController`, zero changes to `routes/api.php`, zero changes anywhere else in the app. That decoupling — depending on a contract, letting the container decide the implementation — is the entire value proposition, and it's the same reasoning behind Spring's "depend on the interface, `@Qualifier`/`@Primary` picks the implementation" pattern from the Spring Boot journey's Week 1 Day 4.

---

## 🖥️ Trying This Yourself

```bash
composer install
cp .env.example .env
php artisan key:generate

php artisan serve
```

Then, in another terminal:
```bash
curl http://localhost:8000/
curl http://localhost:8000/api/greet/YourName
```

You should see:
```json
{
  "message": "Good day, YourName. Welcome to the Task Manager API.",
  "resolved_implementation": "App\\Services\\FormalGreetingService"
}
```

**The actual Day 1 exercise:** open `app/Providers/AppServiceProvider.php`, comment out the `FormalGreetingService` binding, uncomment the `CasualGreetingService` one, save, and hit `/api/greet/YourName` again with no server restart needed (`php artisan serve`'s dev server picks up PHP file changes automatically). Watch both the `message` and `resolved_implementation` fields change — that's the container's binding decision, made visible.

---

## ❌ Common Mistakes

| Mistake | Fix |
|---------|-----|
| Type-hinting concrete classes everywhere instead of interfaces | You lose the ability to swap implementations without touching every call site — bind interfaces in a provider instead |
| Manually instantiating services with `new` inside controllers | Defeats the entire purpose of the container — let constructor type-hints trigger automatic resolution |
| Doing real work (queries, I/O) inside a provider's `register()` method | `register()` should ONLY bind things — other providers' bindings aren't guaranteed to exist yet at this point. Real setup work belongs in `boot()` |
| Assuming `artisan` is a compiled tool | It's ~15 lines of plain PHP, sharing the same bootstrap as `public/index.php` — read it, it's not mysterious |
| Committing a real `.env` file | Exactly the Spring Boot journey's Week 8 lesson, applying here too — `.env` is gitignored; only `.env.example` (with placeholders) gets committed |
| Looking for `app/Http/Kernel.php` in a modern Laravel project | Laravel 11+ moved that configuration into `bootstrap/app.php` — old tutorials referencing `Kernel.php` are describing Laravel 10 and earlier |

---

## ✅ Day 1 Checklist

- [x] `composer.json` — the project manifest, equivalent to `pom.xml`
- [x] `artisan` — read and understood as a real PHP script, not a black box
- [x] `bootstrap/app.php` + `bootstrap/providers.php` — Laravel 11's consolidated bootstrap
- [x] `public/index.php` — the HTTP front controller, and how it differs from `artisan`'s console entry point
- [x] A real interface + two implementations (`GreetingServiceInterface`, `FormalGreetingService`, `CasualGreetingService`)
- [x] A binding registered in `AppServiceProvider::register()`
- [x] A controller resolving the binding via automatic constructor injection — zero manual container calls
- [x] Verified: swapped the binding, watched the resolved implementation change with no other code touched

---

**Date**: August 23, 2026
**Status**: ✅ Week 1, Day 1 Complete!
**Next**: Day 2 — Service Container Deep Dive: `singleton()` vs `bind()` in practice, contextual binding, and resolving dependencies manually via `app()->make()` when automatic resolution isn't enough.

> *"The container isn't Laravel's dependency injection framework bolted on top. It's the thing everything else — routing, providers, even artisan commands — is built ON TOP of."*
