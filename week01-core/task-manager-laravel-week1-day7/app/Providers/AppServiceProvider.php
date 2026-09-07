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
        // singleton() — SequentialIdGenerator holds a counter, so reuse
        // vs. fresh instantiation is genuinely observable (Week 1 Day 2).
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
        // See that provider for the full explanation. The two CONTEXTUAL
        // bindings just above are untouched by this move — they're tracked
        // separately by the container and keep behaving exactly as they
        // did in Day 2/3.

        // ── Week 1 Day 4 — typed config, bound as a singleton ────────────
        // singleton() is the right lifetime here (not bind()) for a
        // different reason than SequentialIdGenerator's — this object
        // holds no mutable state that could leak between requests. It's
        // just genuinely wasteful to re-read from config() and
        // reconstruct this object on every single resolution when the
        // underlying config values never change during the app's
        // lifetime. One instance, built once, reused everywhere.
        //
        // The closure form of singleton() (rather than passing a class
        // name as the second argument, like every other binding in this
        // file) is used here because TaskManagerConfig needs its
        // fromConfig() factory method called, not a bare `new
        // TaskManagerConfig()` — the container can't know how to
        // construct this object on its own, since its constructor takes
        // plain scalars, not resolvable class dependencies.
        $this->app->singleton(
            TaskManagerConfig::class,
            fn () => TaskManagerConfig::fromConfig(),
        );
    }

    public function boot(): void
    {
        // ── Week 1 Day 5 — defining a named rate limiter ─────────────────
        // This is Laravel's BUILT-IN throttling middleware being
        // configured — no custom middleware class needed for this at all
        // (writing your OWN middleware from scratch is Week 2 Day 4).
        // 'api-demo' is just a label; routes/api.php applies it to a
        // route group via ->middleware('throttle:api-demo'), and every
        // route in that group shares this exact same rule, declared once
        // here instead of repeated per-route.
        RateLimiter::for('api-demo', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });
    }
}
