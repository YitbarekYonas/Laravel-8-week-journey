<?php

namespace App\Services;

use App\Services\Contracts\GreetingServiceInterface;

// The third of three GreetingServiceInterface implementations — this is
// the direct Laravel equivalent of the Spring Boot journey's
// "3 GreetingStrategy implementations (Formal, Casual, Festive)" mini-
// project exercise. Nothing about this class is special or different in
// KIND from FormalGreetingService/CasualGreetingService — it's the same
// contract, a third tone. What's new for today is HOW one of these three
// gets selected — see GreetingStrategyServiceProvider.
class FestiveGreetingService implements GreetingServiceInterface
{
    public function greet(string $name): string
    {
        return "🎉 Woohoo, {$name}! Let's turn this task list into a celebration!";
    }
}
