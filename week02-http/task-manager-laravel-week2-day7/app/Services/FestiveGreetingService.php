<?php

namespace App\Services;

use App\Services\Contracts\GreetingServiceInterface;

class FestiveGreetingService implements GreetingServiceInterface
{
    public function greet(string $name): string
    {
        return "🎉 Woohoo, {$name}! Let's turn this task list into a celebration!";
    }
}
