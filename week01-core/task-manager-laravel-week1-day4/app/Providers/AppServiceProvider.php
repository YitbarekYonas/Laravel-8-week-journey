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

        // Global fallback binding — anything that depends on
        // GreetingServiceInterface WITHOUT a contextual rule of its own
        // gets this. Deliberately bound to CasualGreetingService here —
        // DIFFERENT from GreetingController's contextual override
        // (FormalGreetingService) — so that resolving via the Greeting
        // facade (app/Facades/Greeting.php) produces a VISIBLY different
        // result than GreetingController does, proving the facade truly
        // has no "consuming class" context and falls through to this
        // global rule instead of accidentally matching one of the
        // contextual ones.
        $this->app->bind(
            GreetingServiceInterface::class,
            CasualGreetingService::class,
        );

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
        //
    }
}
