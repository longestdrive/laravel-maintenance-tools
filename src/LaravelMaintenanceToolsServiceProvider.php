<?php

namespace Longestdrive\LaravelMaintenanceTools;

use Illuminate\Console\Scheduling\Schedule;
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

    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        // Register scheduled tasks
        $this->app->booted(function () {
            $this->registerScheduledTasks($this->app->make(Schedule::class));
        });
    }

    /**
     * Register scheduled tasks with the Laravel scheduler.
     *
     * @return void
     */
    protected function registerScheduledTasks(Schedule $schedule)
    {
        // Get schedule configuration
        $scheduleConfig = config('maintenance-tools.schedule', []);

        // Schedule clean:tempfiles command
        if (isset($scheduleConfig['clean_temp_files']) && $scheduleConfig['clean_temp_files']['enabled'] ?? true) {
            $this->scheduleCommand($schedule, 'clean:tempfiles', $scheduleConfig['clean_temp_files']);
        }

        // Schedule logs:clean-old command
        if (isset($scheduleConfig['clean_old_logs']) && $scheduleConfig['clean_old_logs']['enabled'] ?? true) {
            $this->scheduleCommand($schedule, 'logs:clean-old', $scheduleConfig['clean_old_logs']);
        }
    }

    /**
     * Schedule a command based on configuration.
     *
     * @return void
     */
    protected function scheduleCommand(Schedule $schedule, string $command, array $config)
    {
        $frequency = $config['frequency'] ?? 'weekly';
        $time = $config['time'] ?? '00:00';

        // Create the scheduled task
        $scheduledTask = $schedule->command($command);

        // Apply the frequency
        switch ($frequency) {
            case 'daily':
                $scheduledTask->dailyAt($time);
                break;

            case 'weekly':
                $day = $config['day'] ?? 'monday';
                $scheduledTask->weeklyOn(
                    $this->getDayNumber($day),
                    $time
                );
                break;

            case 'monthly':
                $scheduledTask->monthlyOn(1, $time);
                break;

            case 'quarterly':
                $scheduledTask->quarterlyOn(1, $time);
                break;

            case 'yearly':
                $scheduledTask->yearlyOn(1, 1, $time);
                break;

            case 'custom':
                if (isset($config['cron'])) {
                    $scheduledTask->cron($config['cron']);
                }
                break;
        }
    }

    /**
     * Get the day number for a day name.
     */
    protected function getDayNumber(string $day): int
    {
        $days = [
            'sunday' => 0,
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 3,
            'thursday' => 4,
            'friday' => 5,
            'saturday' => 6,
        ];

        return $days[strtolower($day)] ?? 1; // Default to Monday
    }
}
