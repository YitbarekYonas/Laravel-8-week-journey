<?php

// ── A grouped, typed-in-spirit config file ──────────────────────────────
// This is the direct equivalent of the Spring Boot journey's
// @ConfigurationProperties(prefix="mail") pattern — one file, one logical
// group of related settings, each backed by an env() call with a sane
// default. Everything in this file becomes reachable via
// config('task_manager.pagination.default_page_size'), etc.
//
// ── The one hard rule every config file must follow ─────────────────────
// This file must return a PLAIN array of scalars/arrays only — no
// closures, no objects, nothing that can't be serialized. `php artisan
// config:cache` combines every config/*.php file into one big compiled
// array and writes it to bootstrap/cache/config.php; a closure can't
// survive that process, and Laravel will throw a clear error the moment
// you try to cache a config file that contains one. If you need computed
// or object-shaped config, do that computation in a service class instead
// (see app/Support/TaskManagerConfig.php) and let THAT read from config()
// — never make config() itself return something uncacheable.
return [

    'greeting' => [
        'default_tone' => env('GREETING_DEFAULT_TONE', 'formal'),
    ],

    'pagination' => [
        'default_page_size' => (int) env('PAGINATION_DEFAULT_SIZE', 15),
        'max_page_size' => (int) env('PAGINATION_MAX_SIZE', 100),
    ],

    // env() is always safe to call HERE — inside a config file. This is
    // the one place it's the correct tool, because config files are
    // exactly what config:cache compiles: by the time caching happens,
    // every env() call in every config file has already been resolved
    // into a plain value and baked into the cached array. Calling env()
    // ANYWHERE ELSE in application code (controllers, services) is the
    // anti-pattern this project deliberately demonstrates — see
    // ConfigDemoController.
    'maintenance_mode_message' => env(
        'MAINTENANCE_MODE_MESSAGE',
        'We will be back shortly.'
    ),

    // Environment-specific behavior — branching directly on the
    // environment name, still entirely inside a config file, still
    // completely safe to cache (this resolves to a plain `true`/`false`
    // at cache time, exactly like every other value here).
    'verbose_config_logging' => env('APP_ENV', 'production') === 'local',

];
