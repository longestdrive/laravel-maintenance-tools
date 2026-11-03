# Laravel Maintenance Tools

[![Latest Version on Packagist](https://img.shields.io/packagist/v/longestdrive/laravel-maintenance-tools.svg?style=flat-square)](https://packagist.org/packages/longestdrive/laravel-maintenance-tools)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/longestdrive/laravel-maintenance-tools/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/longestdrive/laravel-maintenance-tools/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/longestdrive/laravel-maintenance-tools/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/longestdrive/laravel-maintenance-tools/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/longestdrive/laravel-maintenance-tools.svg?style=flat-square)](https://packagist.org/packages/longestdrive/laravel-maintenance-tools)

A set of Artisan commands for keeping a Laravel application tidy during development and deployment.

Laravel apps collect clutter over time: temporary upload folders fill up, rotated log archives pile up in `storage/logs`, copied classes leave duplicate names, and the `migrations` table drifts out of sync with the migration files. This package gives you a command for each of these jobs. It can also schedule the clean-up commands for you, so housekeeping runs without any extra cron entries.

## Features

| Command | What it does |
|---|---|
| [`clean:tempfiles`](#cleantempfiles) | Empties the temporary directories you configure |
| [`clean:logs`](#cleanlogs) | Deletes archived log files older than a set number of days |
| [`scan:duplicates`](#scanduplicates) | Finds duplicate PHP class names and file names in a folder |
| [`migration:repair`](#migrationrepair) | Adds migration files missing from the `migrations` table so they're marked as run |
| [`scan:nonTestMethods`](#scannontestmethods) | Finds test methods that rely on the `@test` docblock annotation |

`clean:tempfiles` and `clean:logs` are scheduled to run weekly by default. You can change or turn off the schedule in the config file.

## Requirements

- PHP 8.3+
- Laravel 11, 12 or 13

> **Note:** Laravel 11 is end-of-life and no longer gets security fixes. It's still supported here, but upgrading your application to Laravel 12 or 13 is strongly recommended.

## Installation

Install the package via Composer:

```bash
composer require longestdrive/laravel-maintenance-tools
```

The service provider is registered automatically through Laravel package discovery.

To change the defaults, publish the config file to `config/maintenance-tools.php`:

```bash
php artisan vendor:publish --tag="laravel-maintenance-tools-config"
```

## Configuration

The published config file looks like this:

```php
return [
    // Directories emptied by clean:tempfiles
    'temp_files' => [
        storage_path('temp'),
        storage_path('app/temp'),
    ],

    // File extension clean:logs looks for in storage/logs
    'log_file_extension' => '.gz',

    // Days of log archives clean:logs keeps (overridable with --days)
    'log_retention_days' => 30,

    // Automatic scheduling (see "Scheduling" below)
    'schedule' => [
        'clean_temp_files' => [
            'enabled' => true,
            'frequency' => 'weekly',
            'day' => 'monday',
            'time' => '01:00',
        ],
        'clean_old_logs' => [
            'enabled' => true,
            'frequency' => 'weekly',
            'day' => 'monday',
            'time' => '02:00',
        ],
    ],
];
```

## Usage

### `clean:tempfiles`

Removes all files **and subdirectories** inside each directory listed in `maintenance-tools.temp_files`. The directories themselves are kept.

```bash
php artisan clean:tempfiles
```

If a configured directory doesn't exist, or a file can't be removed, the command logs an error and exits with a non-zero status. That makes it safe to use in deployment scripts.

> **Warning:** everything inside the configured directories is deleted. Only list directories that hold disposable files.

### `clean:logs`

Deletes log files in `storage/logs` that match `maintenance-tools.log_file_extension` (default `.gz`) and were last modified longer ago than `maintenance-tools.log_retention_days` (default 30). It's meant for compressed log archives left behind by log rotation. Your active `.log` files are left alone.

The retention period from the config also applies to scheduled runs. Pass `--days` to override it for a single run.

```bash
# Use log_retention_days from the config (default 30)
php artisan clean:logs

# Keep the last 7 days
php artisan clean:logs --days=7
```

### `scan:duplicates`

Recursively scans a folder for PHP files that share a class name or file name. This is handy for catching copy-paste leftovers or classes that will clash once namespaces are flattened.

```bash
php artisan scan:duplicates app
```

The folder is relative to your project root. Results are written to a numbered report, e.g. `storage/logs/duplicate_scan_1.log`:

```
Duplicate class names:
  UserService:
    - /var/www/app/Services/UserService.php
    - /var/www/app/Legacy/UserService.php
Duplicate file names:
  Helpers.php:
    - /var/www/app/Support/Helpers.php
    - /var/www/app/Http/Helpers.php
```

### `migration:repair`

Compares the files in `database/migrations` with the `migrations` table. Any migration missing from the table is inserted into a new batch, which marks it as already run.

```bash
php artisan migration:repair
```

Use this when the schema is already in place but the `migrations` table isn't, for example after importing a database dump or restoring from a backup. Afterwards, `php artisan migrate` won't try to re-run those migrations.

> **Warning:** this command **does not run** any migrations. It only records them as run. Make sure the schema really matches before you use it. The `migrations` table must already contain at least one entry.

### `scan:nonTestMethods`

Scans every PHP file under `tests/` and lists public methods that are marked as tests only by a `@test` docblock annotation, rather than a `test` name prefix.

```bash
php artisan scan:nonTestMethods
```

PHPUnit 12 no longer reads docblock annotations, so these tests are silently skipped. Use the report to find them and either rename them with a `test` prefix or add the `#[Test]` attribute. Results are written to a numbered report, e.g. `storage/logs/non_test_methods_1.log`:

```
Class: UserTest in /var/www/tests/Feature/UserTest.php
  - it_creates_a_user
  - it_deletes_a_user
```

## Scheduling

The package registers `clean:tempfiles` and `clean:logs` with Laravel's scheduler. By default they run every Monday, at 01:00 and 02:00 respectively. Laravel's scheduler must be running for this to work:

```
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

Each entry under `schedule` in the config supports:

- `enabled`: `true` or `false`
- `frequency`: `daily`, `weekly`, `monthly`, `quarterly`, `yearly` or `custom`
- `day`: day of the week, for `weekly` only
- `time`: 24-hour `HH:MM`
- `cron`: a cron expression, for `custom` only

```php
'clean_old_logs' => [
    'enabled' => true,
    'frequency' => 'custom',
    'cron' => '0 2 * * 1,4', // 2:00 AM on Mondays and Thursdays
],
```

To see the registered tasks, run `php artisan schedule:list`. For more examples, see [SCHEDULING.md](SCHEDULING.md).

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security Vulnerabilities

If you discover a security vulnerability, please email raygun.crane@gmail.com rather than opening a public issue.

## Credits

- [Longestdrive](https://github.com/longestdrive)
- [All Contributors](../../contributors)

Built on [spatie/laravel-package-tools](https://github.com/spatie/laravel-package-tools).

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
