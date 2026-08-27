<?php

namespace App\Services\Contracts;

// ── Why this interface exists ───────────────────────────────────────────
// Nothing in this file does any real work — that's the point. Controllers
// (and other classes) should depend on THIS contract, never on the
// concrete FormalGreetingService class directly. The service container
// is what decides, at resolution time, which concrete class actually
// satisfies this contract (see AppServiceProvider::register()).
//
// This is the same principle Spring's ApplicationContext gives you via
// @Autowired on an interface type with @Qualifier/@Primary picking the
// implementation — Laravel's container does the equivalent job, just
// configured in a service provider instead of an annotation.
interface GreetingServiceInterface
{
    public function greet(string $name): string;
}
