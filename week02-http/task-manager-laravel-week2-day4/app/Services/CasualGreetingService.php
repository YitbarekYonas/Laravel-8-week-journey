<?php

namespace App\Services;

use App\Services\Contracts\GreetingServiceInterface;

class CasualGreetingService implements GreetingServiceInterface
{
    public function greet(string $name): string
    {
        return "Hey {$name}! Ready to get some tasks done?";
    }
}
