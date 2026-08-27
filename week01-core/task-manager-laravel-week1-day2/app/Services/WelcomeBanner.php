<?php

namespace App\Services;

// This class takes a plain string constructor argument — not a type-hinted
// dependency the container can resolve on its own (it has no idea what
// string value you want). Automatic resolution works great when every
// constructor parameter is itself a resolvable class/interface; it has
// nothing to offer when a parameter is just "a piece of data only the
// caller knows." Resolving THIS class requires telling the container what
// value to use — see the container:demo command for how.
class WelcomeBanner
{
    public function __construct(
        private readonly string $appName,
        private readonly string $tagline = 'Task management, done right.',
    ) {}

    public function render(): string
    {
        return "=== {$this->appName} ===\n{$this->tagline}";
    }
}
