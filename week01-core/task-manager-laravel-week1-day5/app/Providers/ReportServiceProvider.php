<?php

namespace App\Providers;

use App\Services\Contracts\ReportGeneratorInterface;
use App\Services\ReportGeneratorService;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

// ── Deferred providers ───────────────────────────────────────────────────
//
// Every OTHER provider in this app (AppServiceProvider,
// StartupBannerServiceProvider) has its register() method called on
// EVERY single request, unconditionally — even for a request that will
// never touch anything those providers bind. For a handful of providers
// binding cheap, stateless services, that's completely fine.
//
// Implementing DeferrableProvider changes that: it tells Laravel "don't
// call my register() at all during normal bootstrap — instead, read my
// provides() method to learn WHICH bindings I'm responsible for, and only
// call register() the very first time something actually asks for one of
// those bindings." For a request that never resolves
// ReportGeneratorInterface, this provider's register() NEVER RUNS at all
// for that request — genuinely skipped, not just cached.
//
// This matters most for providers that are expensive to register (heavy
// setup work) or bind services most requests never touch — exactly what
// ReportGeneratorService stands in for here.
class ReportServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        // Writes a proof file the instant this actually runs — purely for
        // this Day 3 demonstration, so "did register() run yet?" is
        // externally observable via HTTP instead of something you'd have
        // to take on faith. See DeferredDemoController for how this gets
        // checked from both BEFORE and AFTER the interface is resolved.
        file_put_contents(
            storage_path('logs/deferred-provider-proof.log'),
            'ReportServiceProvider::register() ran at '.now()->toDateTimeString().PHP_EOL,
            FILE_APPEND,
        );

        $this->app->singleton(
            ReportGeneratorInterface::class,
            ReportGeneratorService::class,
        );
    }

    /**
     * ── The mechanism that makes deferral possible ──────────────────────
     * Laravel scans every provider implementing DeferrableProvider and
     * builds a map of {binding => provider} from this method's return
     * value — WITHOUT calling register() on any of them yet. When
     * something later resolves ReportGeneratorInterface for the first
     * time, the container looks it up in that map, finds
     * ReportServiceProvider is responsible for it, calls THIS provider's
     * register() at that exact moment (and only that one provider's —
     * not every deferred provider in the app), and then resolves the
     * binding as normal.
     */
    public function provides(): array
    {
        return [
            ReportGeneratorInterface::class,
        ];
    }
}
