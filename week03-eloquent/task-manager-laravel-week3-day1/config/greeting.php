<?php

// The config file that actually changes application behavior — read
// directly by GreetingStrategyServiceProvider to pick the active
// GreetingServiceInterface implementation.
return [

    'active_strategy' => env('GREETING_ACTIVE_STRATEGY', 'casual'),

    'valid_strategies' => ['formal', 'casual', 'festive'],

];
