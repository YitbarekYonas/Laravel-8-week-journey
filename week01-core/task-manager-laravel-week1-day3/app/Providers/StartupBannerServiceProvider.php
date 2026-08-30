<?php

namespace App\Providers;

use App\Services\Contracts\GreetingServiceInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

// ── Why THIS provider's logic can't safely live in register() ──────────
//
// This provider wants to RESOLVE GreetingServiceInterface — a binding
// that AppServiceProvider is responsible for — and use it to log a
// startup message.
//
// register() runs for EVERY provider, in the order they're listed in
// bootstrap/providers.php, BEFORE any provider's boot() runs. If this
// provider is listed AFTER AppServiceProvider (it currently is — see
// bootstrap/providers.php), resolving GreetingServiceInterface inside
// THIS class's OWN register() would happen to work right now. But that
// correctness is an ACCIDENT of list ordering, not a guarantee — reorder
// the two entries in bootstrap/providers.php, or introduce a third
// provider that registers its OWN bindings in between, and a resolution
// attempted from inside register() could run before AppServiceProvider's
// binding has been registered yet, and silently break.
//
// boot() removes that fragility entirely: Laravel guarantees EVERY
// provider's register() has finished — for the ENTIRE application,
// regardless of listed order — before ANY provider's boot() runs. By the
// time this method executes, it is safe to resolve ANYTHING any other
// provider bound, with zero dependency on list ordering.
class StartupBannerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Deliberately empty — this provider has nothing of its own to
        // BIND into the container. It only wants to USE something another
        // provider already bound, which is exactly the boot()-only use
        // case this class exists to demonstrate.
    }

    public function boot(): void
    {
        // Safe here, unsafe (by luck only) in register() — see the class
        // comment above. This resolves the interface AppServiceProvider
        // bound, with a hard guarantee that binding already exists by
        // this point, regardless of provider list order.
        $greetingService = $this->app->make(GreetingServiceInterface::class);

        Log::info('StartupBannerServiceProvider::boot() ran successfully.', [
            'resolved_greeting_implementation' => get_class($greetingService),
            'sample_output' => $greetingService->greet('Laravel'),
            'proof' => 'This resolution only works reliably because it ran '.
                'in boot(), after every provider\'s register() had already '.
                'completed — not in register(), where it would depend on '.
                'accidental provider list ordering.',
        ]);
    }
}
