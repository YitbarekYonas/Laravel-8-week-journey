<?php

namespace App\Services;

use App\Services\Contracts\GreetingServiceInterface;

// One concrete implementation of the contract above. Nothing Laravel-
// specific about this class at all — it's a plain PHP class. The only
// place Laravel enters the picture is in AppServiceProvider, where this
// class gets WIRED to the interface it implements.
class FormalGreetingService implements GreetingServiceInterface
{
    public function greet(string $name): string
    {
        return "Good day, {$name}. Welcome to the Task Manager API.";
    }
}
