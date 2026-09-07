<?php

namespace App\Providers;

use App\Services\CasualGreetingService;
use App\Services\Contracts\GreetingServiceInterface;
use App\Services\FestiveGreetingService;
use App\Services\FormalGreetingService;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

// ── Week 1 Day 7 Mini-Project — a THIRD way to pick an implementation ───
//
// Days 1–3 picked a GreetingServiceInterface implementation two ways:
//   1. A single hardcoded global bind() (Day 1)
//   2. CONTEXTUAL bindings, keyed to which CLASS is asking (Day 2–3)
//
// This provider adds a third: the active implementation is picked by a
// CONFIG VALUE. Change GREETING_ACTIVE_STRATEGY in .env, and every
// consumer that relies on the plain global default — no contextual rule
// of its own — gets a different concrete class, with zero PHP source
// changes.
//
// ── This SUPERSEDES the old global bind() ────────────────────────────────
// AppServiceProvider used to have a plain bind(GreetingServiceInterface,
// CasualGreetingService) global default (see that file's comment for
// what changed). That line has been REMOVED — this provider is now the
// single place responsible for the global default. The two CONTEXTUAL
// bindings AppServiceProvider still defines, for GreetingController and
// CasualGreetingController specifically, are UNAFFECTED by any of this —
// Laravel's container tracks contextual bindings separately from the
// plain global default, so those two controllers keep their Day 2/3
// behavior exactly as before regardless of what strategy is active here.
class GreetingStrategyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(GreetingServiceInterface::class, function () {
            $strategy = config('greeting.active_strategy');

            return match ($strategy) {
                'formal' => new FormalGreetingService,
                'casual' => new CasualGreetingService,
                'festive' => new FestiveGreetingService,
                // Fail loudly and immediately if .env has a typo or an
                // unsupported value — the same "fail fast" philosophy
                // behind preferring constructor injection over field
                // injection back in Week 1 Day 1's container discussion.
                // A silently-ignored typo here would be a much worse
                // debugging experience than an exception at boot time.
                default => throw new InvalidArgumentException(
                    "Unknown greeting strategy \"{$strategy}\" in ".
                    'config(\'greeting.active_strategy\') — must be one of: '.
                    implode(', ', config('greeting.valid_strategies'))
                ),
            };
        });
    }

    public function boot(): void
    {
        //
    }
}
