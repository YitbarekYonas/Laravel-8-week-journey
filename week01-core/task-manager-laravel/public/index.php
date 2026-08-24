<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

// ── This file is the ENTRY POINT for every single HTTP request ─────────
// A web server (Nginx, or `php artisan serve`'s built-in dev server) is
// configured to route every request to THIS file, regardless of the URL
// path — `/api/tasks`, `/`, `/anything` all land here first. Laravel's
// own router (configured via routes/api.php, routes/web.php) is what then
// decides which controller actually handles the request; this file's job
// is just to boot the framework and hand the request off to it.
//
// Compare this to artisan (the CLI entry point) — both files do the same
// two things (autoload, then boot the app container) but hand control to
// a DIFFERENT kernel afterward: HTTP here, Console there. Same
// application, two different front doors.

define('LARAVEL_START', microtime(true));

// Maintenance mode check — if `php artisan down` was run, a maintenance
// page is served here, before the framework itself even boots, since a
// bootstrap failure is exactly the scenario maintenance mode exists to
// gracefully handle.
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

// Resolve the HTTP kernel (not the console kernel — see artisan for that
// side) and hand the actual incoming request to it. This is where the
// middleware pipeline (Week 1 Day 6) begins: the request passes through
// every applicable middleware layer before reaching a controller, and the
// response passes back through them (in reverse) before being sent here.
$app->handleRequest(Request::capture());
