<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Temporary Files Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the directories that should be cleaned by the clean:tempfiles command
    |
    */

    'temp_files' => [
        storage_path('temp'),
        storage_path('app/temp'),
    ],
    /*
     * log file extension for the clean:oldlogs command
     * This is used to identify which log files should be cleaned
     */
    'log_file_extension' => '.gz',

    /*
    |--------------------------------------------------------------------------
    | Log Files Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the number of days to keep log files
    |
    */
    'log_retention_days' => 30,
];

