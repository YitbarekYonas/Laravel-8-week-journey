<?php

// A minimal config file backing ApiKeyMiddleware's shared-secret gate.
// Following Week 1 Day 4's rule precisely: env() only ever appears inside
// a config file, never directly inside the middleware itself.
return [
    'api_key' => env('DEMO_API_KEY', 'demo-secret-key'),
];
