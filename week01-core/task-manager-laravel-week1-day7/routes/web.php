<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'app' => config('app.name'),
        'status' => 'running',
        'note' => 'Every URL below is also generated dynamically at '
            .'/api/route-links via the route() helper — this list is just '
            .'a hardcoded convenience for browsing.',
        'try' => [
            'week1_mini_project' => [
                '/api/mini-project/greet/YourName (the Week 1 capstone — '
                    .'touches every day this week in one endpoint)',
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
            'task_manager' => [
                '/api/tasks',
                '/api/tasks/1',
                '/api/tasks/abc (constrained — 404 before the controller runs)',
            ],
            'routing_concepts' => [
                '/api/greet-optional (optional param, no segment)',
                '/api/greet-optional/Sam (optional param, with segment)',
                '/api/limited/ping (rate-limited via built-in throttle middleware)',
                '/api/route-links (named routes → real URLs, generated live)',
            ],
            'request_lifecycle' => [
                '/api/lifecycle/trace (watch the middleware pipeline order for real)',
                '/api/lifecycle/plain (no route-specific middleware — check the '
                    .'X-Request-Id header anyway; it\'s global)',
            ],
        ],
    ]);
});
