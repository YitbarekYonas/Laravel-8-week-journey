<?php

namespace App\Http\Controllers;

use App\Support\TaskManagerConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;

class ConfigDemoController extends Controller
{
    public function __construct(
        private readonly TaskManagerConfig $taskManagerConfig,
    ) {}

    public function show(): JsonResponse
    {
        return response()->json([
            'via_typed_config_object' => [
                'greeting_tone' => $this->taskManagerConfig->greetingDefaultTone,
                'pagination_default' => $this->taskManagerConfig->paginationDefaultPageSize,
                'pagination_max' => $this->taskManagerConfig->paginationMaxPageSize,
            ],
            'via_config_helper' => [
                'greeting_tone' => config('task_manager.greeting.default_tone'),
                'app_name' => config('app.name'),
                'app_env' => config('app.env'),
            ],
            'via_env_directly_ANTI_PATTERN' => [
                'app_name' => env('APP_NAME'),
                'warning' => 'This value will silently become null after '
                    .'`php artisan config:cache` runs — env() should NEVER '
                    .'be called outside of a config/*.php file.',
            ],
            'runtime_override_demo' => (function () {
                $before = config('task_manager.pagination.default_page_size');
                Config::set('task_manager.pagination.default_page_size', 999);
                $after = config('task_manager.pagination.default_page_size');

                return ['before' => $before, 'after_runtime_override' => $after];
            })(),
        ]);
    }
}
