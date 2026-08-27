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
        // ═══════════════════════════════════════════════════════════════
        // 1. singleton() vs bind() — SequentialIdGenerator holds state
        // ═══════════════════════════════════════════════════════════════
        // singleton(): the container builds ONE instance, the first time
        // it's asked, and returns that SAME instance for every subsequent
        // resolution during this request/process. SequentialIdGenerator's
        // internal counter genuinely persists across calls because of
        // this — see IdDemoController and ContainerDemoCommand for proof.
        $this->app->singleton(
            IdGeneratorInterface::class,
            SequentialIdGenerator::class,
        );

        // Try this yourself: comment out the singleton() call above and
        // uncomment this bind() instead, then hit /api/ids/demo again.
        // Every "instance #" in the response will now differ, and every
        // counter will restart at 1 — a brand new SequentialIdGenerator,
        // with a brand new counter starting at zero, gets created on
        // EVERY resolution, so state can never accumulate.
        //
        // $this->app->bind(
        //     IdGeneratorInterface::class,
        //     SequentialIdGenerator::class,
        // );

        // ═══════════════════════════════════════════════════════════════
        // 2. Contextual binding — same interface, different class asking
        // ═══════════════════════════════════════════════════════════════
        // GreetingController and CasualGreetingController both type-hint
        // the EXACT SAME GreetingServiceInterface in their constructors.
        // A single global bind() (what Day 1 used) can't tell them apart —
        // it just answers "whoever asks for this interface gets X."
        //
        // ->when(SomeClass::class)->needs(SomeInterface::class)->give(...)
        // lets the container answer differently DEPENDING ON WHO'S ASKING.
        // This is the real-world justification for contextual binding:
        // two different consumers, genuinely different needs, same
        // contract.
        $this->app->when(GreetingController::class)
            ->needs(GreetingServiceInterface::class)
            ->give(FormalGreetingService::class);

        $this->app->when(CasualGreetingController::class)
            ->needs(GreetingServiceInterface::class)
            ->give(CasualGreetingService::class);

        // Note: because both controllers now have an explicit contextual
        // binding covering them, this global bind() further down is DEAD
        // CODE for these two controllers specifically — it would only
        // matter for some THIRD class that also depends on
        // GreetingServiceInterface but has no contextual rule of its own.
        // Kept here deliberately to make that precedence visible:
        // contextual bindings win over the global one whenever both could
        // apply.
        $this->app->bind(
            GreetingServiceInterface::class,
            FormalGreetingService::class,
        );
    }

    public function boot(): void
    {
        //
    }
}
