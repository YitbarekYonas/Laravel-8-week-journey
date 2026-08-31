# Laravel Roadmap — Week 1, Day 4

[![Status](https://img.shields.io/badge/Status-Completed-brightgreen.svg)]()
[![Day](https://img.shields.io/badge/Day-4-blue.svg)]()
[![Topic](https://img.shields.io/badge/Topic-Configuration%20System-orange.svg)]()

> **"`env()` works everywhere, right up until the one command that makes it stop working everywhere except the one place you were told to use it."**

---

## ⚠️ Same Setup Note as Days 1–3

Hand-built Laravel 11 skeleton, no `vendor/` directory (no Packagist access in this sandbox). Run this first:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

---

## 🎯 Learning Objectives

- ✅ Understand the actual division of labor between `.env` and `config/*.php` files
- ✅ Build a typed config object — Laravel's closest equivalent to Spring's `@ConfigurationProperties`
- ✅ Understand exactly what `php artisan config:cache` does, and why it's the one command most likely to silently break code that looked completely fine a minute earlier
- ✅ Understand Laravel's real environment-specific config mechanism (`.env.testing`) and how it genuinely differs from the Spring Boot journey's multi-profile-file approach

---

## 💡 What I Learned Today

### 1. The Actual Division of Labor: `.env` vs `config/*.php`

`.env` holds raw, environment-specific **values** — a database password, a debug flag, a tone string. It is never read directly by application code that wants to *use* a setting. `config/*.php` files are the layer in between: they read from `.env` (via `env()`, with a sane default) once, and organize those raw values into a structured, documented shape:

```php
// config/task_manager.php
return [
    'pagination' => [
        'default_page_size' => (int) env('PAGINATION_DEFAULT_SIZE', 15),
        'max_page_size' => (int) env('PAGINATION_MAX_SIZE', 100),
    ],
];
```

Everywhere else in the app — controllers, services, anywhere — reads through `config('task_manager.pagination.default_page_size')`, never through `env('PAGINATION_DEFAULT_SIZE')` directly. That indirection is the entire point of today's lesson, and it's not a style preference — see section 3.

### 2. A Typed Config Object — Laravel's Answer to `@ConfigurationProperties`

`config('task_manager.pagination.default_page_size')` works, but every call site has to get that dotted string exactly right, with zero IDE autocomplete and zero compile-time safety — a typo just silently returns `null`. `TaskManagerConfig` wraps the whole group into one typed, readonly object instead:

```php
final readonly class TaskManagerConfig
{
    public function __construct(
        public string $greetingDefaultTone,
        public int $paginationDefaultPageSize,
        // ...
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            greetingDefaultTone: config('task_manager.greeting.default_tone'),
            // ...
        );
    }
}
```

Bound as a singleton in `AppServiceProvider` (read once, reused for the app's lifetime — the values never change mid-request), `ConfigDemoController` then just type-hints `TaskManagerConfig` in its constructor and gets real property access with IDE support: `$this->taskManagerConfig->paginationDefaultPageSize`.

**Honest trade-off vs Spring:** this mapping is hand-written — you write `fromConfig()` yourself, and a mismatched key still only fails at runtime, not compile time. Spring's annotation-driven binding catches some of these mistakes earlier. Both are reasonable choices for their respective ecosystems; Laravel just doesn't try to hide that this is a manual pattern, not a framework feature.

### 3. `php artisan config:cache` — What It Actually Does, and What It Breaks

This is the one genuinely dangerous gotcha in today's topic, so it's worth being exact.

`config:cache` reads every `config/*.php` file — with every `env()` call inside them **already resolved** to a plain value — and writes the combined result to a single compiled file, `bootstrap/cache/config.php`. From that point on, **`.env` is not read at all** — not "read and ignored," not "read but overridden" — Laravel skips loading it entirely, for performance, because reading and parsing `.env` on every single request is exactly the cost caching exists to eliminate.

The consequence: **any `env()` call outside of a config file — in a controller, a service, anywhere in application code — silently returns `null`** from the moment the cache exists, with no error, no warning. `ConfigDemoController`'s `via_env_directly_ANTI_PATTERN` key is built specifically to demonstrate this:

```bash
# 1. Before caching — this works fine
curl http://localhost:8000/api/config/demo
# "via_env_directly_ANTI_PATTERN": { "app_name": "Task Manager" }

# 2. Cache the config
php artisan config:cache

# 3. Same request, no code changed at all
curl http://localhost:8000/api/config/demo
# "via_env_directly_ANTI_PATTERN": { "app_name": null }   ← silently broken

# Meanwhile, via_config_helper and via_typed_config_object are COMPLETELY
# UNAFFECTED — config() reads from the cached, compiled array, which
# already has the real value baked in from when the cache was built.

# 4. Undo it
php artisan config:clear
```

**Why this can't be demonstrated inside one script or one PHP process:** `.env` loading happens once, very early, at the start of the framework's bootstrap — before your application code, and even before most service providers, ever run. Caching config doesn't retroactively un-load values already sitting in `$_ENV`/`getenv()` for the *current* process. The difference only becomes visible across genuinely separate process invocations — which is exactly why the steps above use real, separate `curl` calls against a real running `php artisan serve`, rather than anything that could be faked inside a single command.

**The rule, stated plainly:** `env()` is only ever safe inside a `config/*.php` file. Everywhere else, use `config()`.

### 4. Environment-Specific Config — Where Laravel Genuinely Differs from Spring

Worth being precise here, because it's a real difference from the Spring Boot journey's pattern, not just different syntax for the same idea:

- **Spring**: `application-dev.yml`, `application-prod.yml` — multiple named, **committed** profile files, switched via `spring.profiles.active`.
- **Laravel's default**: just **one file**, always named `.env`, **never committed**. Your local machine's `.env` has local values; your production server's `.env` (which you never see in the repo) has production values. Same filename, different git-ignored contents per machine — there's no Laravel convention of committing `.env.production`.
- **The one real exception — testing**: Laravel automatically loads `.env.testing` *instead of* `.env` whenever `APP_ENV=testing` (which `phpunit.xml` sets by default). This project's `.env.testing.example` demonstrates that — copy it to `.env.testing` and Week 7's test suite will run against deliberately different values (a separate tone, a separate database) without touching your real local `.env` at all.

---

## 🖥️ Trying This Yourself

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan serve
```

```bash
curl http://localhost:8000/api/config/demo
```

**The actual Day 4 exercise — the config caching gotcha, for real:**
1. Note the `app_name` value under `via_env_directly_ANTI_PATTERN` in the response above.
2. Change `APP_NAME` in your `.env` file to something different.
3. Hit `/api/config/demo` again — the new value shows up everywhere (nothing cached yet).
4. Run `php artisan config:cache`.
5. Hit `/api/config/demo` one more time — `via_config_helper` and `via_typed_config_object` still show the value that was correct *at cache time*; `via_env_directly_ANTI_PATTERN`'s `app_name` is now `null`.
6. Run `php artisan config:clear` to undo it, confirm everything's back to reading live again.

---

## ❌ Common Mistakes

| Mistake | Fix |
|---------|-----|
| Calling `env()` inside a controller or service | Only ever safe inside `config/*.php` — everywhere else, use `config()` |
| Forgetting to run `config:clear` after changing `.env` locally, then debugging a "value won't update" mystery for an hour | If a config value seems stuck, check whether a stale `bootstrap/cache/config.php` exists |
| Putting a closure or object in a config file's return array | `config:cache` requires every config file to be serializable to a plain array — no closures, no objects |
| Assuming Laravel has committed per-environment config files like Spring's `application-prod.yml` | It doesn't, by default — one gitignored `.env` per machine, with `.env.testing` as the one committed-convention exception |
| Treating `config:cache` as purely a "nice to have" optimization with no behavioral risk | It's genuinely behavior-changing, not just a speed boost — anything relying on live `env()` reads outside config files breaks silently |

---

## ✅ Day 4 Checklist

- [x] `config/task_manager.php` — a grouped config file, `env()` with defaults, entirely closure-free (cacheable)
- [x] `TaskManagerConfig` — a typed, readonly config object, Laravel's answer to `@ConfigurationProperties`, bound as a singleton
- [x] `ConfigDemoController` showing all three access patterns side by side: typed object, `config()` helper, and the `env()` anti-pattern
- [x] `config()` used as a runtime setter (`Config::set(...)`), not just a getter
- [x] The config caching gotcha explained precisely — what actually happens, why it can't be faked in one process, and exact real commands to observe it
- [x] `.env.testing.example` — Laravel's real environment-specific config mechanism, contrasted honestly against the Spring Boot journey's multi-file profile pattern

---

**Date**: August 30, 2026
**Status**: ✅ Week 1, Day 4 Complete!
**Next**: Day 5 — Routing: basic routes, route parameters, named routes, route groups, and middleware — where this project's routes finally grow past a handful of demo endpoints into something resembling the real Task Manager API.

> *"Every config value in this app has exactly one place it's allowed to call env() from. That's not a style rule — it's the actual boundary of what config:cache can protect."*
