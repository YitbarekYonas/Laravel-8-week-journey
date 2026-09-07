<?php

namespace App\Facades;

use App\Services\Contracts\GreetingServiceInterface;
use Illuminate\Support\Facades\Facade;

/**
 * ── What a facade actually is ────────────────────────────────────────────
 *
 * A facade is NOT a static version of a class. Greeting::greet('Sam') does
 * NOT call a static method literally named greet() on this class — this
 * class doesn't even DEFINE a greet() method. What actually happens:
 *
 * 1. PHP can't find a real `greet` static method on this class, so it
 *    falls through to the magic method __callStatic('greet', ['Sam']) —
 *    which this class INHERITS from Illuminate\Support\Facades\Facade,
 *    the real parent class.
 * 2. That inherited __callStatic implementation calls
 *    static::getFacadeRoot() — which internally calls
 *    static::getFacadeAccessor() (defined below) to learn WHAT to
 *    resolve, then asks the service container to resolve it, exactly
 *    the same as app()->make(GreetingServiceInterface::class) would.
 * 3. It then forwards the original call — greet('Sam') — onto THAT
 *    resolved object.
 *
 * So `Greeting::greet('Sam')` and
 * `app(GreetingServiceInterface::class)->greet('Sam')` do EXACTLY the
 * same work. The facade is purely syntax sugar around a container
 * resolution + method call — nothing about it is actually static in the
 * sense the class name suggests.
 *
 * @method static string greet(string $name)
 */
class Greeting extends Facade
{
    /**
     * The only method a facade is REQUIRED to implement. This string is
     * the container binding key that getFacadeRoot() resolves — exactly
     * the same key used in AppServiceProvider's bind() call.
     */
    protected static function getFacadeAccessor(): string
    {
        return GreetingServiceInterface::class;
    }
}
