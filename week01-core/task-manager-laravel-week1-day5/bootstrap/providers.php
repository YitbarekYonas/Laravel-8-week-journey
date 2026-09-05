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
];
