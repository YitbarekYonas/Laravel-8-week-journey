<?php

namespace App\Services\Contracts;

// A second contract, deliberately chosen because its implementation holds
// STATE (a counter). Yesterday's GreetingServiceInterface was stateless —
// every implementation just computed a string from its input, so bind()
// vs singleton() would have been invisible; a fresh instance behaves
// identically to a reused one when there's no state to lose. This
// contract exists specifically to make that distinction observable.
interface IdGeneratorInterface
{
    public function next(): string;
}
