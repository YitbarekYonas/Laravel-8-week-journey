<?php

// A grouped, typed-in-spirit config file — the Laravel equivalent of the
// Spring Boot journey's @ConfigurationProperties(prefix="mail") pattern.
// See app/Support/TaskManagerConfig.php for the typed object built from
// these values.
return [

    'greeting' => [
        'default_tone' => env('GREETING_DEFAULT_TONE', 'formal'),
    ],

    'pagination' => [
        'default_page_size' => (int) env('PAGINATION_DEFAULT_SIZE', 15),
        'max_page_size' => (int) env('PAGINATION_MAX_SIZE', 100),
    ],

    'maintenance_mode_message' => env(
        'MAINTENANCE_MODE_MESSAGE',
        'We will be back shortly.'
    ),

    'verbose_config_logging' => env('APP_ENV', 'production') === 'local',

];
