<?php

// ── The config() helper's home ──────────────────────────────────────────
// Every array key in this file becomes accessible via config('app.name'),
// config('app.env'), etc., anywhere in the codebase. This is Laravel's
// answer to Spring's @ConfigurationProperties — except instead of binding
// to a typed Java class, it's just nested PHP arrays, read through the
// config() helper or the Config facade.
//
// Notice every value below reads from env('SOME_VAR', 'a_default') — the
// SAME pattern from the Spring Boot journey's ${VAR:default} syntax.
// env() reads from the process environment / .env file; the second
// argument is what's used if that variable isn't set at all. Week 1 Day 4
// covers the full config system, including config caching (`php artisan
// config:cache`), which pre-compiles all of this into a single PHP array
// for production — at that point, env() calls in config files stop being
// re-evaluated per-request entirely.
return [

    'name' => env('APP_NAME', 'Task Manager'),

    'env' => env('APP_ENV', 'production'),

    'debug' => (bool) env('APP_DEBUG', false),

    'url' => env('APP_URL', 'http://localhost'),

    'timezone' => 'UTC',

    'locale' => env('APP_LOCALE', 'en'),

    'fallback_locale' => 'en',

    'faker_locale' => 'en_US',

    // The APP_KEY is used for encrypting sessions, cookies, and anything
    // passed through Laravel's Crypt facade. `php artisan key:generate`
    // writes a fresh one into .env — never commit a real key to git,
    // exactly the same "never hardcode secrets" principle from the Spring
    // Boot journey's Week 8.
    'cipher' => 'AES-256-CBC',
    'key' => env('APP_KEY'),
    'previous_keys' => [
        ...array_filter(
            explode(',', env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

];
