<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'app' => config('app.name'),
        'status' => 'running',
        'note' => 'Every URL below is also generated dynamically at '
            .'/api/route-links via the route() helper.',
        'try' => [
            'week1_mini_project' => [
                '/api/mini-project/greet/YourName',
            ],
            'task_manager_resource' => [
                '/api/tasks (GET — index)',
                '/api/tasks/1 (GET — show, via route model binding)',
                '/api/tasks/abc (constrained — 404 before the controller runs)',
                '/api/tasks-summary (single-action controller)',
            ],
            'demo' => [
                '/api/demo/greet/YourName',
                '/api/demo/greet-casual/YourName',
                '/api/demo/greet-facade/YourName',
                '/api/demo/ids',
                '/api/demo/deferred/status',
                '/api/demo/deferred/generate',
                '/api/demo/config',
            ],
            'routing_concepts' => [
                '/api/greet-optional (optional param, single-action controller)',
                '/api/greet-optional/Sam',
                '/api/limited/ping (rate-limited via built-in throttle middleware)',
                '/api/route-links (named routes → real URLs, generated live)',
            ],
            'request_lifecycle' => [
                '/api/lifecycle/trace',
                '/api/lifecycle/plain',
            ],
        ],
    ]);
});
