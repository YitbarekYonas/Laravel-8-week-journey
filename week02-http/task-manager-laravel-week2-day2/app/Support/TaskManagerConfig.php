<?php

namespace App\Support;

final readonly class TaskManagerConfig
{
    public function __construct(
        public string $greetingDefaultTone,
        public int $paginationDefaultPageSize,
        public int $paginationMaxPageSize,
        public string $maintenanceModeMessage,
        public bool $verboseConfigLogging,
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            greetingDefaultTone: config('task_manager.greeting.default_tone'),
            paginationDefaultPageSize: config('task_manager.pagination.default_page_size'),
            paginationMaxPageSize: config('task_manager.pagination.max_page_size'),
            maintenanceModeMessage: config('task_manager.maintenance_mode_message'),
            verboseConfigLogging: config('task_manager.verbose_config_logging'),
        );
    }
}
