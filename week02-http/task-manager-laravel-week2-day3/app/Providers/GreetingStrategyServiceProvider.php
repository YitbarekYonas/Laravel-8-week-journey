<?php

namespace App\Providers;

use App\Services\CasualGreetingService;
use App\Services\Contracts\GreetingServiceInterface;
use App\Services\FestiveGreetingService;
use App\Services\FormalGreetingService;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

class GreetingStrategyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(GreetingServiceInterface::class, function () {
            $strategy = config('greeting.active_strategy');

            return match ($strategy) {
                'formal' => new FormalGreetingService,
                'casual' => new CasualGreetingService,
                'festive' => new FestiveGreetingService,
                default => throw new InvalidArgumentException(
                    "Unknown greeting strategy \"{$strategy}\" in ".
                    'config(\'greeting.active_strategy\') — must be one of: '.
                    implode(', ', config('greeting.valid_strategies'))
                ),
            };
        });
    }

    public function boot(): void
    {
        //
    }
}
