<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

// ── Console (artisan) routes — closure-based commands ───────────────────
// This is where you can define simple artisan commands as closures,
// without creating a full Command class in app/Console/Commands/. Ships
// with one default command from a fresh Laravel install: `php artisan
// inspire`, kept here as a working example of the syntax.
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
