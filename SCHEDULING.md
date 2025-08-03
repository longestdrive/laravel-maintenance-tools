# Laravel Maintenance Tools - Scheduling

This document explains how to use the scheduling functionality in the Laravel Maintenance Tools package.

## Overview

The Laravel Maintenance Tools package now includes scheduling functionality for the following commands:

- `clean:tempfiles`: Cleans temporary files from specified directories
- `logs:clean-old`: Deletes old log files based on retention period

By default, these commands are scheduled to run weekly on Monday, with `clean:tempfiles` at 1:00 AM and `logs:clean-old` at 2:00 AM.

## Configuration

You can customize the scheduling of these commands by modifying the `schedule` section in the `config/maintenance-tools.php` configuration file:

```php
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
```

### Configuration Options

For each command, you can configure:

- `enabled`: Set to `true` to enable scheduling, `false` to disable
- `frequency`: How often to run the command. Available options:
  - `daily`: Run every day
  - `weekly`: Run once a week on the specified day
  - `monthly`: Run once a month on the first day
  - `quarterly`: Run once every three months
  - `yearly`: Run once a year
  - `custom`: Use a custom cron expression
- `day`: The day of the week to run the command (only used for `weekly` frequency)
  - Available options: `sunday`, `monday`, `tuesday`, `wednesday`, `thursday`, `friday`, `saturday`
- `time`: The time to run the command in 24-hour format (HH:MM)
- `cron`: A custom cron expression (only used when `frequency` is set to `custom`)

## Examples

### Run Daily

```php
'clean_temp_files' => [
    'enabled' => true,
    'frequency' => 'daily',
    'time' => '01:00',
],
```

### Run Weekly on Wednesday

```php
'clean_old_logs' => [
    'enabled' => true,
    'frequency' => 'weekly',
    'day' => 'wednesday',
    'time' => '02:00',
],
```

### Run Monthly

```php
'clean_temp_files' => [
    'enabled' => true,
    'frequency' => 'monthly',
    'time' => '01:00',
],
```

### Custom Schedule (Multiple Days)

```php
'clean_old_logs' => [
    'enabled' => true,
    'frequency' => 'custom',
    'cron' => '0 2 * * 1,4', // Run at 2:00 AM on Monday and Thursday
],
```

## How It Works

The package registers the scheduled tasks in the service provider's `boot` method. When your Laravel application boots, the package checks the configuration and schedules the commands accordingly.

The scheduling is handled by Laravel's built-in scheduler, so you need to make sure you have set up the scheduler in your application as described in the [Laravel documentation](https://laravel.com/docs/scheduling).

Typically, this involves adding a single Cron entry to your server that runs the Laravel scheduler every minute:

```
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## Disabling Scheduling

If you don't want to use the scheduling functionality, you can disable it for each command:

```php
'clean_temp_files' => [
    'enabled' => false,
],
'clean_old_logs' => [
    'enabled' => false,
],
```

Or you can remove the entire `schedule` section from the configuration file.
