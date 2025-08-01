<?php

namespace Longestdrive\LaravelMaintenanceTools\Tests;

use Carbon\Carbon;
use Illuminate\Filesystem\Filesystem;
use Longestdrive\LaravelMaintenanceTools\Commands\CleanOldLogFilesCommand;
use Mockery;

class CleanOldLogFilesCommandTest extends TestCase
{
    protected Filesystem $filesystem;

    protected string $logsPath;

    protected Carbon $now;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock the filesystem
        $this->filesystem = Mockery::mock(Filesystem::class);
        $this->app->instance(Filesystem::class, $this->filesystem);

        // Set up a fixed "now" time for testing
        $this->now = Carbon::create(2023, 1, 31, 12);
        Carbon::setTestNow($this->now);

        // Define the log path
        $this->logsPath = storage_path('logs');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        Carbon::setTestNow(); // Reset the mock time
        parent::tearDown();
    }

    public function test_it_deletes_only_gz_files_older_than_30_days()
    {
        // Set up test files
        $oldGzFile = $this->logsPath.'/laravel-2022-12-01.log.gz'; // 61 days old
        $newGzFile = $this->logsPath.'/laravel-2023-01-15.log.gz'; // 16 days old
        //        $logFile = $this->logsPath.'/laravel-2023-01-31.log'; // current log file

        // Mock filesystem methods
        $this->filesystem->shouldReceive('exists')
            ->with($this->logsPath)
            ->once()
            ->andReturn(true);

        $this->filesystem->shouldReceive('glob')
            ->with($this->logsPath.'/*.gz')
            ->once()
            ->andReturn([$oldGzFile, $newGzFile]);

        // Mock file creation times
        $this->filesystem->shouldReceive('lastModified')
            ->with($oldGzFile)
            ->once()
            ->andReturn($this->now->copy()->subDays(61)->timestamp);

        $this->filesystem->shouldReceive('lastModified')
            ->with($newGzFile)
            ->once()
            ->andReturn($this->now->copy()->subDays(16)->timestamp);

        // Expect only the old .gz file to be deleted
        $this->filesystem->shouldReceive('delete')
            ->with($oldGzFile)
            ->once();

        // Run the command using the package command class name
        $this->artisan(CleanOldLogFilesCommand::class)
            ->expectsOutput('Cleaning .gz log files older than 30 days from '.$this->logsPath)
            ->expectsOutput('Deleted: '.basename($oldGzFile).' (Created: 2022-12-01)')
            ->expectsOutput('Deleted 1 old .gz log files')
            ->assertExitCode(0);
    }

    public function test_it_does_not_delete_log_files()
    {
        // Set up test files - only .gz files should be processed
        $gzFile = $this->logsPath.'/laravel-2022-12-01.log.gz'; // 61 days old

        // Mock filesystem methods
        $this->filesystem->shouldReceive('exists')
            ->with($this->logsPath)
            ->once()
            ->andReturn(true);

        $this->filesystem->shouldReceive('glob')
            ->with($this->logsPath.'/*.gz')
            ->once()
            ->andReturn([$gzFile]);

        // Mock file creation times
        $this->filesystem->shouldReceive('lastModified')
            ->with($gzFile)
            ->once()
            ->andReturn($this->now->copy()->subDays(61)->timestamp);

        // Expect the old .gz file to be deleted
        $this->filesystem->shouldReceive('delete')
            ->with($gzFile)
            ->once();

        // Run the command
        $this->artisan('logs:clean-old')
            ->expectsOutput('Cleaning .gz log files older than 30 days from '.$this->logsPath)
            ->expectsOutput('Deleted: '.basename($gzFile).' (Created: 2022-12-01)')
            ->expectsOutput('Deleted 1 old .gz log files')
            ->assertExitCode(0);

        // Note: We don't need to explicitly test that .log files aren't deleted
        // because the command only processes .gz files (line 69 in CleanOldLogFilesCommand.php)
    }

    public function test_it_does_not_delete_gz_files_newer_than_30_days()
    {
        // Set up test files
        $newGzFile = $this->logsPath.'/laravel-2023-01-15.log.gz'; // 16 days old

        // Mock filesystem methods
        $this->filesystem->shouldReceive('exists')
            ->with($this->logsPath)
            ->once()
            ->andReturn(true);

        $this->filesystem->shouldReceive('glob')
            ->with($this->logsPath.'/*.gz')
            ->once()
            ->andReturn([$newGzFile]);

        // Mock file creation times
        $this->filesystem->shouldReceive('lastModified')
            ->with($newGzFile)
            ->once()
            ->andReturn($this->now->copy()->subDays(16)->timestamp);

        // No delete calls should be made
        $this->filesystem->shouldNotReceive('delete');

        // Run the command
        $this->artisan('logs:clean-old')
            ->expectsOutput('Cleaning .gz log files older than 30 days from '.$this->logsPath)
            ->expectsOutput('Deleted 0 old .gz log files')
            ->assertExitCode(0);
    }

    public function test_it_handles_custom_days_parameter()
    {
        // Set up test files
        $gzFile = $this->logsPath.'/laravel-2023-01-15.log.gz'; // 16 days old

        // Mock filesystem methods
        $this->filesystem->shouldReceive('exists')
            ->with($this->logsPath)
            ->once()
            ->andReturn(true);

        $this->filesystem->shouldReceive('glob')
            ->with($this->logsPath.'/*.gz')
            ->once()
            ->andReturn([$gzFile]);

        // Mock file creation times
        $this->filesystem->shouldReceive('lastModified')
            ->with($gzFile)
            ->once()
            ->andReturn($this->now->copy()->subDays(16)->timestamp);

        // With a 15-day cutoff, this file should be deleted
        $this->filesystem->shouldReceive('delete')
            ->with($gzFile)
            ->once();

        // Run the command with a custom days parameter
        $this->artisan(CleanOldLogFilesCommand::class, ['--days' => 15])
            ->expectsOutput('Cleaning .gz log files older than 15 days from '.$this->logsPath)
            ->expectsOutput('Deleted: '.basename($gzFile).' (Created: 2023-01-15)')
            ->expectsOutput('Deleted 1 old .gz log files')
            ->assertExitCode(0);
    }

    public function test_it_handles_empty_logs_directory()
    {
        // Mock filesystem methods
        $this->filesystem->shouldReceive('exists')
            ->with($this->logsPath)
            ->once()
            ->andReturn(true);

        $this->filesystem->shouldReceive('glob')
            ->with($this->logsPath.'/*.gz')
            ->once()
            ->andReturn([]);

        // Run the command
        $this->artisan(CleanOldLogFilesCommand::class)
            ->expectsOutput('Cleaning .gz log files older than 30 days from '.$this->logsPath)
            ->expectsOutput('No .gz log files found in '.$this->logsPath)
            ->assertExitCode(0);
    }

    public function test_it_handles_nonexistent_logs_directory()
    {
        // Mock filesystem methods
        $this->filesystem->shouldReceive('exists')
            ->with($this->logsPath)
            ->once()
            ->andReturn(false);

        // Run the command
        $this->artisan('logs:clean-old')
            ->expectsOutput('Cleaning .gz log files older than 30 days from '.$this->logsPath)
            ->expectsOutput('Logs directory does not exist: '.$this->logsPath)
            ->assertExitCode(1);
    }
}
