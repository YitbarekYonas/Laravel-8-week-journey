<?php

// ── The first real database config in this project ──────────────────────
// Nothing in the app has needed this file until today — Task has been a
// plain in-memory PHP class since Week 2 Day 1. Migrations are the first
// thing that actually needs to connect to a real database, so this file
// exists now specifically to back `php artisan migrate`.
return [

    // sqlite by default — the easiest possible path to actually SEE
    // migrations run: no separate database server to install or
    // configure, just a single file. Postgres is fully defined below too
    // (matching this project's earlier .env.example intent, and where a
    // real deployment would eventually point — see the Week 8 roadmap
    // days), switchable with one .env line.
    'default' => env('DB_CONNECTION', 'sqlite'),

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DB_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'task_manager'),
            'username' => env('DB_USERNAME', 'postgres'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ],

    ],

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

];
