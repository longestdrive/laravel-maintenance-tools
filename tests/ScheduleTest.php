<?php

declare(strict_types=1);

namespace Longestdrive\LaravelMaintenanceTools\Tests;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Config;
use Longestdrive\LaravelMaintenanceTools\LaravelMaintenanceToolsServiceProvider;

class ScheduleTest extends TestCase
{
    protected Schedule $schedule;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a fresh Schedule instance for each test
        $this->schedule = $this->app->make(Schedule::class);

        // Clear any existing events
        $this->clearScheduleEvents();
    }

    /**
     * Clear existing schedule events using reflection
     */
    protected function clearScheduleEvents(): void
    {
        $reflection = new \ReflectionClass($this->schedule);
        $eventsProperty = $reflection->getProperty('events');
        $eventsProperty->setAccessible(true);
        $eventsProperty->setValue($this->schedule, []);
    }

    /**
     * Call the protected registerScheduledTasks method using reflection
     */
    protected function callRegisterScheduledTasks(array $config): void
    {
        // Set the configuration
        Config::set('maintenance-tools.schedule', $config);

        // Get the service provider instance from the application
        $provider = $this->app->getProvider(LaravelMaintenanceToolsServiceProvider::class);

        // Use reflection to call the protected method
        $reflection = new \ReflectionClass($provider);
        $method = $reflection->getMethod('registerScheduledTasks');
        $method->setAccessible(true);
        $method->invoke($provider, $this->schedule);
    }

    /**
     * Test that commands are scheduled with default configuration
     */
    public function test_commands_are_scheduled_with_default_config(): void
    {
        // Call the registerScheduledTasks method with default configuration
        $this->callRegisterScheduledTasks([
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
        ]);

        // Get the scheduled events
        $events = $this->schedule->events();

        // Assert that we have two scheduled events
        $this->assertCount(2, $events);

        // Find the clean:tempfiles event
        $cleanTempFilesEvent = $this->findEventByCommand($events, 'clean:tempfiles');
        $this->assertNotNull($cleanTempFilesEvent, 'clean:tempfiles command should be scheduled');

        // Verify the cron expression for clean:tempfiles (weekly on Monday at 01:00)
        $this->assertEquals('0 1 * * 1', $cleanTempFilesEvent->expression);

        // Find the logs:clean-old event
        $cleanOldLogsEvent = $this->findEventByCommand($events, 'clean:logs');
        $this->assertNotNull($cleanOldLogsEvent, 'clean:logs command should be scheduled');

        // Verify the cron expression for logs:clean-old (weekly on Monday at 02:00)
        $this->assertEquals('0 2 * * 1', $cleanOldLogsEvent->expression);
    }

    /**
     * Test that commands are scheduled with daily frequency
     */
    public function test_commands_are_scheduled_with_daily_frequency(): void
    {
        // Call the registerScheduledTasks method with daily frequency configuration
        $this->callRegisterScheduledTasks([
            'clean_temp_files' => [
                'enabled' => true,
                'frequency' => 'daily',
                'time' => '03:30',
            ],
        ]);

        // Get the scheduled events
        $events = $this->schedule->events();

        // Find the clean:tempfiles event
        $cleanTempFilesEvent = $this->findEventByCommand($events, 'clean:tempfiles');
        $this->assertNotNull($cleanTempFilesEvent, 'clean:tempfiles command should be scheduled');

        // Verify the cron expression for clean:tempfiles (daily at 03:30)
        $this->assertEquals('30 3 * * *', $cleanTempFilesEvent->expression);
    }

    /**
     * Test that commands are scheduled with custom cron expression
     */
    public function test_commands_are_scheduled_with_custom_cron(): void
    {
        // Call the registerScheduledTasks method with custom cron configuration
        $this->callRegisterScheduledTasks([
            'clean_old_logs' => [
                'enabled' => true,
                'frequency' => 'custom',
                'cron' => '0 4 * * 1,4', // Run at 4:00 AM on Monday and Thursday
            ],
        ]);

        // Get the scheduled events
        $events = $this->schedule->events();

        // Find the logs:clean-old event
        $cleanOldLogsEvent = $this->findEventByCommand($events, 'clean:logs');
        $this->assertNotNull($cleanOldLogsEvent, 'clean:logs command should be scheduled');

        // Verify the cron expression for logs:clean-old (custom cron)
        $this->assertEquals('0 4 * * 1,4', $cleanOldLogsEvent->expression);
    }

    /**
     * Test that disabled commands are not scheduled
     */
    public function test_disabled_commands_are_not_scheduled(): void
    {
        // Call the registerScheduledTasks method with disabled commands configuration
        $this->callRegisterScheduledTasks([
            'clean_temp_files' => [
                'enabled' => false,
                'frequency' => 'weekly',
                'day' => 'monday',
                'time' => '01:00',
            ],
            'clean_old_logs' => [
                'enabled' => false,
                'frequency' => 'weekly',
                'day' => 'monday',
                'time' => '02:00',
            ],
        ]);

        // Get the scheduled events
        $events = $this->schedule->events();

        // Assert that no events are scheduled
        $this->assertCount(0, $events);
    }

    /**
     * Helper method to find an event by command
     */
    protected function findEventByCommand(array $events, string $command): ?object
    {
        foreach ($events as $event) {
            if (strpos($event->command, $command) !== false) {
                return $event;
            }
        }

        return null;
    }
}
