<?php

namespace App\Http\Controllers;

use App\Support\TaskManagerConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;

class ConfigDemoController extends Controller
{
    // Automatic resolution again (Week 1 Day 1–2) — the container
    // resolves this from the singleton bound in AppServiceProvider. This
    // controller never calls config() directly for these values at all.
    public function __construct(
        private readonly TaskManagerConfig $taskManagerConfig,
    ) {}

    public function show(): JsonResponse
    {
        return response()->json([

            // ── Pattern 1: the typed config object (preferred) ──────────
            'via_typed_config_object' => [
                'greeting_tone' => $this->taskManagerConfig->greetingDefaultTone,
                'pagination_default' => $this->taskManagerConfig->paginationDefaultPageSize,
                'pagination_max' => $this->taskManagerConfig->paginationMaxPageSize,
            ],

            // ── Pattern 2: the config() helper directly (also fine) ─────
            // Perfectly correct — just less convenient than the typed
            // object once a value is used in more than one place, since
            // every call site has to get the dotted string key exactly
            // right with no IDE autocomplete or type safety.
            'via_config_helper' => [
                'greeting_tone' => config('task_manager.greeting.default_tone'),
                'app_name' => config('app.name'),
                'app_env' => config('app.env'),
            ],

            // ── Pattern 3: env() called directly in application code ────
            // THE ANTI-PATTERN. This works completely fine RIGHT NOW,
            // while config hasn't been cached — env() just reads straight
            // from the process environment / .env file every time it's
            // called, from anywhere. The moment `php artisan config:cache`
            // runs, this stops being true: Laravel skips loading .env
            // entirely once a config cache file exists (it's not
            // "consulted and ignored" — it's not read AT ALL, for
            // performance), so every env() call outside of a config file
            // returns null from that point on, silently, with no error.
            // See the README for the exact commands to observe this
            // happen for real.
            'via_env_directly_ANTI_PATTERN' => [
                'app_name' => env('APP_NAME'),
                'warning' => 'This value will silently become null after '
                    .'`php artisan config:cache` runs — env() should NEVER '
                    .'be called outside of a config/*.php file.',
            ],

            // ── config() as a SETTER, not just a getter ──────────────────
            // Passing an array to config() overrides the value for the
            // REST of this request's lifetime — genuinely useful for
            // tests (override a setting for one test case) or for
            // request-scoped feature flags. This override is NOT written
            // back to any file and NOT persisted anywhere — it's pure
            // in-memory state, gone the instant this request finishes.
            'runtime_override_demo' => (function () {
                $before = config('task_manager.pagination.default_page_size');
                Config::set('task_manager.pagination.default_page_size', 999);
                $after = config('task_manager.pagination.default_page_size');

                return ['before' => $before, 'after_runtime_override' => $after];
            })(),

        ]);
    }
}
