<?php

namespace App\Providers;

use App\Services\Contracts\GreetingServiceInterface;
use App\Services\FormalGreetingService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * ── register() vs boot() — Day 3's distinction, previewed here ──────
     *
     * register() is ONLY for binding things into the container. It runs
     * for every provider BEFORE any provider's boot() method runs. This
     * ordering guarantee is exactly why binding logic belongs here, not
     * in boot(): if this class tried to RESOLVE another service in
     * register() (instead of just binding), that other service's own
     * provider might not have registered its bindings yet.
     *
     * boot() (further down) is for anything that needs the FULL container
     * already wired up — event listeners, view composers, etc. Week 1
     * Day 3 covers this distinction, and the deferred-provider
     * optimization built on top of it, in full depth.
     */
    public function register(): void
    {
        // ── The actual container binding ────────────────────────────────
        // "Whenever something asks the container for a
        // GreetingServiceInterface, hand it a FormalGreetingService
        // instance." Nothing else in the app needs to know this decision
        // was made — GreetingController (below) just type-hints the
        // interface and receives a working instance automatically.
        //
        // bind() creates a NEW instance every time it's resolved.
        // singleton() (not used here) would reuse the same instance for
        // the lifetime of the request — the right choice depends on
        // whether the service holds any per-request state.
        $this->app->bind(
            GreetingServiceInterface::class,
            FormalGreetingService::class,
        );

        // ── Try this yourself ───────────────────────────────────────────
        // Comment out the bind() call above and uncomment this one
        // instead — then hit the /api/greet/{name} route again. Nothing
        // in GreetingController or routes/api.php changes at all; only
        // the response text changes. That's the entire value proposition
        // of depending on an interface instead of a concrete class.
        //
        // $this->app->bind(
        //     GreetingServiceInterface::class,
        //     \App\Services\CasualGreetingService::class,
        // );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
