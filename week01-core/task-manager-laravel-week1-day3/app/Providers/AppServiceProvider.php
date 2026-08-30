<?php

namespace App\Providers;

use App\Http\Controllers\CasualGreetingController;
use App\Http\Controllers\GreetingController;
use App\Services\CasualGreetingService;
use App\Services\Contracts\GreetingServiceInterface;
use App\Services\Contracts\IdGeneratorInterface;
use App\Services\FormalGreetingService;
use App\Services\SequentialIdGenerator;
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
    }

    public function boot(): void
    {
        //
    }
}
