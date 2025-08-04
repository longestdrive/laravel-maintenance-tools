# This is my package laravel-maintenance-tools

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ray-crane/laravel-maintenance-tools.svg?style=flat-square)](https://packagist.org/packages/ray-crane/laravel-maintenance-tools)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/ray-crane/laravel-maintenance-tools/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/ray-crane/laravel-maintenance-tools/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/ray-crane/laravel-maintenance-tools/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/ray-crane/laravel-maintenance-tools/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/ray-crane/laravel-maintenance-tools.svg?style=flat-square)](https://packagist.org/packages/ray-crane/laravel-maintenance-tools)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Support us
Developed using spatie/laravel-package-skeleton


[//]: # ()
[//]: # ([<img src="https://github-ads.s3.eu-central-1.amazonaws.com/laravel-maintenance-tools.jpg?t=1" width="419px" />]&#40;https://spatie.be/github-ad-click/laravel-maintenance-tools&#41;)

[//]: # ()
[//]: # (We invest a lot of resources into creating [best in class open source packages]&#40;https://spatie.be/open-source&#41;. You can support us by [buying one of our paid products]&#40;https://spatie.be/open-source/support-us&#41;.)

[//]: # ()
[//]: # (We highly appreciate you sending us a postcard from your hometown, mentioning which of our package&#40;s&#41; you are using. You'll find our address on [our contact page]&#40;https://spatie.be/about-us&#41;. We publish all received postcards on [our virtual postcard wall]&#40;https://spatie.be/open-source/postcards&#41;.)

## Installation

You can install the package via composer:

```bash
composer require longestdrive/laravel-maintenance-tools
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="laravel-maintenance-tools-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-maintenance-tools-config"
```

This is the contents of the published config file:

```php
return [
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="laravel-maintenance-tools-views"
```

## Usage

### Commands

The package provides several maintenance commands:

- `clean:tempfiles`: Cleans temporary files from specified directories
- `clean:logs`: Deletes old log files based on retention period
- `find:duplicates`: Finds duplicate classes and files in your application
- `repair:migrations`: Repairs the migrations table
- `scan:nontestmethods`: Scans for non-test methods in test classes

You can run these commands using Artisan:

```bash
php artisan clean:tempfiles
php artisan clean:logs
php artisan find:duplicates
php artisan repair:migrations
php artisan scan:nontestmethods
```

### Scheduling

The package now includes scheduling functionality for the `clean:tempfiles` and `logs:clean-old` commands. By default, these commands are scheduled to run weekly on Monday, with `clean:tempfiles` at 1:00 AM and `logs:clean-old` at 2:00 AM.

You can customize the scheduling in the configuration file:

```php
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
```

For more details on scheduling options, see [SCHEDULING.md](SCHEDULING.md).

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Longestdrive](https://github.com/longestdrive)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
