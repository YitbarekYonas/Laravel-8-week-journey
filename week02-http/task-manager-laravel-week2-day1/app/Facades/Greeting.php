<?php

namespace App\Facades;

use App\Services\Contracts\GreetingServiceInterface;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string greet(string $name)
 */
class Greeting extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return GreetingServiceInterface::class;
    }
}
