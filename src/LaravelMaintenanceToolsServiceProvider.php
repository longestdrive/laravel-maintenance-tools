<?php

namespace Longestdrive\LaravelMaintenanceTools;

use Longestdrive\LaravelMaintenanceTools\Commands\CleanOldLogFilesCommand;
use Longestdrive\LaravelMaintenanceTools\Commands\CleanTemporaryFilesCommand;
use Longestdrive\LaravelMaintenanceTools\Commands\FindDuplicateClassesAndFiles;
use Longestdrive\LaravelMaintenanceTools\Commands\RepairMigrationTableCommand;
use Longestdrive\LaravelMaintenanceTools\Commands\ScanNonTestMethods;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelMaintenanceToolsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-maintenance-tools')
            ->hasConfigFile('maintenance-tools')
            ->hasViews()
            ->hasMigration('create_laravel_maintenance_tools_table')
            ->hasCommands([
                CleanOldLogFilesCommand::class,
                CleanTemporaryFilesCommand::class,
                FindDuplicateClassesAndFiles::class,
                RepairMigrationTableCommand::class,
                ScanNonTestMethods::class,
            ]);
    }
}
