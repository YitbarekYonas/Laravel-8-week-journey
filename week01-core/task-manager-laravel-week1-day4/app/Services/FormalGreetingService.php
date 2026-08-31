<?php

namespace App\Services;

use App\Services\Contracts\GreetingServiceInterface;

class FormalGreetingService implements GreetingServiceInterface
{
    public function greet(string $name): string
    {
        return "Good day, {$name}. Welcome to the Task Manager API.";
    }
}
