<?php

// ── The config file that actually changes application behavior ─────────
// Unlike config/task_manager.php's greeting.default_tone (Day 4 — purely
// illustrative, never actually consulted by any binding), THIS value is
// read directly by GreetingStrategyServiceProvider to decide which
// concrete GreetingServiceInterface implementation the container hands
// out by default. Change GREETING_ACTIVE_STRATEGY in .env, and the
// active strategy changes — no PHP code touched, no redeploy of source
// files needed.
return [

    // One of: 'formal', 'casual', 'festive'.
    'active_strategy' => env('GREETING_ACTIVE_STRATEGY', 'casual'),

    // The provider validates against this list and fails loudly
    // (InvalidArgumentException) if active_strategy isn't one of these —
    // a typo in .env should be caught immediately at boot, not silently
    // produce confusing behavior later.
    'valid_strategies' => ['formal', 'casual', 'festive'],

];
