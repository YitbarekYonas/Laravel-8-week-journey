<?php

namespace App\Services;

use App\Services\Contracts\GreetingServiceInterface;

// A second, different implementation of the same contract. With only ONE
// implementation, binding an interface to it can look like pointless
// ceremony — "why not just type-hint the concrete class directly?" This
// second implementation is what makes the answer concrete: swapping which
// class the container hands out is a ONE-LINE change in AppServiceProvider,
// touching zero controllers, zero call sites, anywhere in the app.
class CasualGreetingService implements GreetingServiceInterface
{
    public function greet(string $name): string
    {
        return "Hey {$name}! Ready to get some tasks done?";
    }
}
