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

    /*
    |--------------------------------------------------------------------------
    | Scheduling Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the schedule for maintenance commands
    | Available frequencies: daily, weekly, monthly, quarterly, yearly
    | For custom cron expressions, use the 'custom' frequency with a 'cron' value
    | Set 'enabled' to false to disable scheduling for a command
    |
    */
    'schedule' => [
        'clean_temp_files' => [
            'enabled' => true,
            'frequency' => 'weekly',
            'day' => 'monday', // Only used for weekly frequency
            'time' => '01:00',
            // For custom cron expressions
            // 'frequency' => 'custom',
            // 'cron' => '0 1 * * 1', // Run at 1:00 AM every Monday
        ],
        'clean_old_logs' => [
            'enabled' => true,
            'frequency' => 'weekly',
            'day' => 'monday', // Only used for weekly frequency
            'time' => '02:00',
            // For custom cron expressions
            // 'frequency' => 'custom',
            // 'cron' => '0 2 * * 1', // Run at 2:00 AM every Monday
        ],
    ],
];

