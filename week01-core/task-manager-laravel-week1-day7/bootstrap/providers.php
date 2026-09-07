<?php

// Every provider listed here participates in the app's bootstrap — but
// "listed here" and "register() runs immediately on every request" are
// NOT the same thing. ReportServiceProvider (below) implements
// DeferredProvider, which changes WHEN its register() actually executes:
// Laravel reads its provides() method to learn "this provider is
// responsible for ReportGeneratorInterface," and only calls register()
// the first time something actually resolves that interface — not on
// every request regardless of whether it's needed. See
// app/Providers/ReportServiceProvider.php for the full explanation.
return [
    App\Providers\AppServiceProvider::class,
    App\Providers\StartupBannerServiceProvider::class,
    App\Providers\ReportServiceProvider::class,
    // Week 1 Day 7 — the mini-project's dedicated provider. Order relative
    // to AppServiceProvider no longer matters for GreetingServiceInterface
    // specifically, now that AppServiceProvider's OWN competing global
    // bind() has been removed (see that file) — this is the only provider
    // left setting the global default, so there's nothing left for list
    // order to race against.
    App\Providers\GreetingStrategyServiceProvider::class,
];
