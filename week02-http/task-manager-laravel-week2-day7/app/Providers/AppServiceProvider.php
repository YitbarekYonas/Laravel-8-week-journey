<?php

namespace App\Providers;

use App\Http\Controllers\CasualGreetingController;
use App\Http\Controllers\GreetingController;
use App\Services\CasualGreetingService;
use App\Services\Contracts\GreetingServiceInterface;
use App\Services\Contracts\IdGeneratorInterface;
use App\Services\FormalGreetingService;
use App\Services\SequentialIdGenerator;
use App\Support\TaskManagerConfig;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // singleton() — SequentialIdGenerator holds a counter (Week 1 Day 2).
        $this->app->singleton(
            IdGeneratorInterface::class,
            SequentialIdGenerator::class,
        );

        // Contextual binding — same interface, different concrete class
        // depending on which controller is asking (Week 1 Day 2).
        $this->app->when(GreetingController::class)
            ->needs(GreetingServiceInterface::class)
            ->give(FormalGreetingService::class);

        $this->app->when(CasualGreetingController::class)
            ->needs(GreetingServiceInterface::class)
            ->give(CasualGreetingService::class);

        // ── Global fallback binding — MOVED as of Week 1 Day 7 ───────────
        // This used to be a hardcoded bind(GreetingServiceInterface,
        // CasualGreetingService) right here. As of the Day 7 mini-project,
        // that responsibility now belongs to GreetingStrategyServiceProvider
        // instead, which picks the global default from config('greeting.
        // active_strategy') rather than a value hardcoded in PHP source.
        // The two CONTEXTUAL bindings above are untouched by this move.

        // Week 1 Day 4 — typed config, bound as a singleton.
        $this->app->singleton(
            TaskManagerConfig::class,
            fn () => TaskManagerConfig::fromConfig(),
        );
    }

    public function boot(): void
    {
        // Week 1 Day 5 — a named rate limiter for the built-in throttle
        // middleware.
        RateLimiter::for('api-demo', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });
    }
}
