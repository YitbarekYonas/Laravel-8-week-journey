<?php

namespace App\Console\Commands;

use App\Services\Contracts\GreetingServiceInterface;
use App\Services\Contracts\IdGeneratorInterface;
use App\Services\WelcomeBanner;
use Illuminate\Console\Command;

// Registered automatically — Laravel discovers any Command subclass under
// app/Console/Commands/ without needing to be listed anywhere by hand.
// Run with: php artisan container:demo
class ContainerDemoCommand extends Command
{
    protected $signature = 'container:demo';

    protected $description = 'Demonstrates manual service container resolution techniques';

    public function handle(): void
    {
        $this->info('── 1. Resolving a bound interface manually ──');
        // Same technique as IdDemoController — app()->make() resolves
        // exactly what's registered in AppServiceProvider, no different
        // from automatic constructor injection except that WE decide
        // exactly when the resolution happens.
        $greeting = app()->make(GreetingServiceInterface::class);
        $this->line('Resolved: '.get_class($greeting));
        $this->line($greeting->greet('Console User'));

        $this->newLine();
        $this->info('── 2. Resolving with explicit constructor parameters ──');
        // WelcomeBanner::class is NOT bound to anything in
        // AppServiceProvider — the container has never heard of it. That's
        // fine for classes with only auto-resolvable dependencies (the
        // container can just instantiate them directly), but WelcomeBanner
        // needs an $appName string it has no way to guess. The second
        // argument to make() supplies exactly the parameters the container
        // can't infer on its own — everything else in the constructor
        // (if there were other type-hinted dependencies) would STILL be
        // auto-resolved normally; this only overrides what you explicitly
        // name.
        $banner = app()->make(WelcomeBanner::class, [
            'appName' => config('app.name'),
        ]);
        $this->line($banner->render());

        $this->newLine();
        $this->info('── 3. Singleton identity, proven from the console ──');
        // Resolving the SAME singleton from an entirely different entry
        // point (a console command instead of an HTTP controller) still
        // returns the same behavior contract: one shared instance for
        // this process's lifetime. Run this command twice in a row (two
        // separate PHP processes) and the counter resets — singletons
        // live for one request/process, not across separate runs.
        $ids = app()->make(IdGeneratorInterface::class);
        $this->line($ids->next());
        $this->line($ids->next());
        $this->line('(Two calls, same process — counter should have incremented.)');
    }
}
