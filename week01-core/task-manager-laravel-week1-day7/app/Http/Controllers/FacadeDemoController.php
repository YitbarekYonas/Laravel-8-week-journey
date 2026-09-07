<?php

namespace App\Http\Controllers;

use App\Facades\Greeting;
use Illuminate\Http\JsonResponse;

class FacadeDemoController extends Controller
{
    public function greet(string $name): JsonResponse
    {
        // Ordinary facade usage — reads exactly like a static call.
        $message = Greeting::greet($name);

        // getFacadeRoot() is inherited from the base Facade class and is
        // the actual mechanism __callStatic uses internally: it resolves
        // getFacadeAccessor()'s binding key from the container and
        // returns the resolved OBJECT (not a string, not a class name —
        // the literal instance). Calling it directly here, rather than
        // just trusting the static call worked, is what makes the
        // resolved implementation externally verifiable.
        $resolvedInstance = Greeting::getFacadeRoot();

        return response()->json([
            'message' => $message,
            'facade_resolved_to' => get_class($resolvedInstance),
            'explanation' => 'Greeting::greet() and Greeting::getFacadeRoot() '
                .'both trigger the exact same container resolution — '
                .'getFacadeRoot() just lets us inspect the resolved object '
                .'directly instead of only seeing the forwarded method\'s '
                .'return value.',
            'compare_to' => [
                'note' => 'GreetingController (a real controller class, resolved '
                    .'as a constructor dependency) gets FormalGreetingService via '
                    .'a CONTEXTUAL binding specific to that class (Week 1 Day 2). '
                    .'This facade call has no "consuming class" context at all — '
                    .'it\'s a bare static call, not a constructor injection — so '
                    .'it falls through to the GLOBAL default instead. As of the '
                    .'Week 1 Day 7 mini-project, that global default is no longer '
                    .'a hardcoded bind() — it\'s picked by '
                    .'GreetingStrategyServiceProvider from config(\'greeting.'
                    .'active_strategy\'), currently "'.config('greeting.active_strategy').'" via .env. Hit '
                    .'GET /api/demo/greet/{name} and compare "resolved_implementation" '
                    .'there against "facade_resolved_to" here — they genuinely '
                    .'differ (by default), proving contextual bindings only apply '
                    .'when resolving a constructor dependency of a specific, named '
                    .'class, not to a facade\'s bare static call. Change '
                    .'GREETING_ACTIVE_STRATEGY in .env to "formal" and this '
                    .'difference disappears — the facade would then match '
                    .'GreetingController by coincidence, not because contextual '
                    .'binding suddenly applies to it.',
            ],
        ]);
    }
}
