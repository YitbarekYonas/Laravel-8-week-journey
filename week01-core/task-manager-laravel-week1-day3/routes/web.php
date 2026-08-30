<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'app' => config('app.name'),
        'status' => 'running',
        'try' => [
            '/api/greet/YourName',
            '/api/greet-casual/YourName',
            '/api/greet-facade/YourName',
            '/api/ids/demo',
            '/api/deferred/status',
            '/api/deferred/generate',
        ],
    ]);
});
