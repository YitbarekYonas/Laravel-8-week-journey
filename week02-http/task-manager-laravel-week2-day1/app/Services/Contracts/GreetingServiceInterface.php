<?php

namespace App\Services\Contracts;

interface GreetingServiceInterface
{
    public function greet(string $name): string;
}
