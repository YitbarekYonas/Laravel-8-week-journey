<?php

use Illuminate\Support\Facades\Route;

// ── Web routes ────────────────────────────────────────────────────────
// This project is building an API (routes/api.php is where the real work
// happens), so this file stays minimal — just enough to confirm the app
// boots correctly when visited in a browser. Unlike routes/api.php, this
// router group gets sessions, CSRF protection, and cookie-based state —
// the tools a browser-rendered app needs that a stateless JSON API
// deliberately opts out of.
Route::get('/', function () {
    return response()->json([
        'app' => config('app.name'),
        'status' => 'running',
        'try' => '/api/greet/YourName',
    ]);
});
