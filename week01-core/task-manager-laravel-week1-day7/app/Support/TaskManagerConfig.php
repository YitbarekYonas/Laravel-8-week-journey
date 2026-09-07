<?php

namespace App\Support;

// ── Laravel's closest equivalent to Spring's @ConfigurationProperties ───
//
// Laravel doesn't have a built-in mechanism that binds a whole config
// group to a typed class automatically the way Spring's
// @ConfigurationProperties(prefix="mail") does. What it DOES have is
// exactly what you're looking at: a plain, readonly PHP class,
// constructed once from config() values, and bound into the container as
// a singleton (see AppServiceProvider) so the REST of the app can depend
// on a typed object instead of scattering raw config('task_manager.x.y')
// string-key lookups across every controller and service that needs a
// setting.
//
// The trade-off, compared to Spring: this mapping is hand-written, not
// generated — you write fromConfig() yourself, and a typo in a config
// key here fails at RUNTIME (when fromConfig() runs), not at compile
// time. Spring's approach catches some of those mistakes earlier, at the
// cost of more annotation machinery. Both are reasonable trade-offs for
// their respective ecosystems.
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
