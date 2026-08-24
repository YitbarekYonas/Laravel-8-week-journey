<?php

use App\Http\Controllers\GreetingController;
use Illuminate\Support\Facades\Route;

// ── API routes ────────────────────────────────────────────────────────
// bootstrap/app.php registers this file under the 'api' router, which
// means every route here is automatically prefixed with /api and gets
// the 'api' middleware group applied (stateless, no CSRF, no sessions —
// suited to a JSON API rather than a browser-rendered app). Week 5 will
// add Sanctum's auth:sanctum middleware into this same group for
// protected routes.

Route::get('/greet/{name}', [GreetingController::class, 'greet']);

// Week 2 Day 1 introduces Route::apiResource(...) for full CRUD routing
// in one line — this file will grow into the real Task Manager API from
// there. Today's single route exists purely to make the service
// container binding (AppServiceProvider) observable over real HTTP.
