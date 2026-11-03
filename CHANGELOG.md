# Changelog

All notable changes to `laravel-maintenance-tools` will be documented in this file.

## 2.0.2 - 2026-11-03

### Fixed

- CI: the Laravel 11 test jobs failed to install because Composer blocks packages with security advisories, and every Laravel 11 release (now end-of-life) is affected. The L11 jobs now set `COMPOSER_NO_SECURITY_BLOCKING`, so the package is tested against Laravel 11 again. Laravel 12 and 13 jobs are unaffected.

### Documentation

- README notes that Laravel 11 is end-of-life and recommends upgrading to Laravel 12 or 13.

## 2.0.1 - 2026-11-03

### Fixed

- `clean:logs` now respects the `log_retention_days` config value (previously ignored). The `--days` option still overrides it per run.
- `clean:logs` output and log messages now show the configured `log_file_extension` instead of always saying `.gz`, and refer to the command as `clean:logs` instead of `logs:clean-old`.
- `scan:nonTestMethods` description now matches its behaviour: it finds public methods relying on the `@test` docblock annotation, which PHPUnit 12 no longer runs.

### Removed

- Broken migration and views publishing (`laravel-maintenance-tools-migrations` / `laravel-maintenance-tools-views` tags). The migration stub was an empty placeholder whose filename didn't match the registered name, and the package has no views.

### Documentation

- Rewrote the README with a full description of each command, configuration and scheduling.
- Updated the package description in `composer.json`, and the command name in `SCHEDULING.md`.

## 2.0.0 - 2026-11-03

### Breaking changes

- Dropped support for Laravel 10. The package now requires Laravel 11, 12 or 13.
- Renamed the `logs:clean-old` command to `clean:logs` for consistency across commands.

### Added

- Laravel 13 support.

### Changed

- Dev dependencies: Orchestra Testbench `^9.0||^10.0||^11.0`, Pest `^3.0||^4.0`, Larastan `^3.0`, PHPStan extensions `^2.0`, Collision `^8.1.1`.
- CI test matrix now covers Laravel 11, 12 and 13.

## First release of tools - 2025-08-04

First release of tools used to help clean a Laravel package during deployment and development
