# Changelog

All notable changes to `laravel-maintenance-tools` will be documented in this file.

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
