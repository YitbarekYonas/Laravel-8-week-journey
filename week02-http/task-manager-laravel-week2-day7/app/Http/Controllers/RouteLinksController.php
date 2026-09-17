<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class RouteLinksController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'note' => 'Every link below came from route(\'name\', [...params]), '
                .'never a hardcoded URL string.',
            'links' => [
                'tasks_index' => route('tasks.index'),
                'tasks_show_example' => route('tasks.show', ['task' => 3]),
                'tasks_summary' => route('tasks.summary'),
                'greet_optional_with_name' => route('greet-optional', ['name' => 'Sam']),
                'greet_optional_without_name' => route('greet-optional'),
                'demo_greet' => route('demo.greet', ['name' => 'Sam']),
                'demo_config' => route('demo.config'),
                'limited_ping' => route('limited.ping'),
                'lifecycle_trace' => route('lifecycle.trace'),
                'lifecycle_plain' => route('lifecycle.plain'),
                'mini_project_greet' => route('mini-project.greet', ['name' => 'Sam']),
                'input_demo' => route('input-demo'),
                'status_demo' => route('status-demo').'?code=409',
                'header_demo' => route('header-demo'),
                'tasks_mark_as_done_example' => route('tasks.markAsDone', ['task' => 2]),
                'secure_ping' => route('secure.ping').' (requires X-Api-Key header — see .env DEMO_API_KEY)',
            ],
        ]);
    }
}
