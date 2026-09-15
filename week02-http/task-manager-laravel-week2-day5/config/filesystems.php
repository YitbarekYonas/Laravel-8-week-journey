<?php

// A minimal filesystems config — just enough for the 'local' disk used by
// TaskController::store()'s file upload handling. A full Laravel install
// ships a fuller version of this file (a 'public' disk, S3 driver
// options, etc.) — trimmed here to exactly what this project actually
// uses today.
return [

    'default' => env('FILESYSTEM_DISK', 'local'),

    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],
    ],

];
