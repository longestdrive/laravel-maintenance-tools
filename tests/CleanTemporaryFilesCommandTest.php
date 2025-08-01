<?php

declare(strict_types=1);

namespace Longestdrive\LaravelMaintenanceTools\Tests;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Longestdrive\LaravelMaintenanceTools\Commands\CleanTemporaryFilesCommand;

class CleanTemporaryFilesCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Log::spy();
    }


    public function test_it_cleans_temp_files_when_directory_exists_and_is_cleaned()
    {
        $filesystem = $this->createMock(Filesystem::class);
        $dir = base_path('storage/temp');
        Config::set('maintenance-tools.temp_files', [$dir]);
        $filesystem->method('exists')->with($dir)->willReturn(true);
        $filesystem->expects($this->once())->method('cleanDirectory')->with($dir);
        $filesystem->method('files')->with($dir)->willReturn([]);

        // Bind the mock to the container and register the command
        $this->app->instance(Filesystem::class, $filesystem);
        $this->app->singleton(CleanTemporaryFilesCommand::class, function () use ($filesystem) {
            return new CleanTemporaryFilesCommand($filesystem);
        });
        $this->app['Illuminate\\Contracts\\Console\\Kernel']->registerCommand($this->app->make(CleanTemporaryFilesCommand::class));

        $this->artisan('clean:tempfiles')
            ->expectsOutput('Temp files cleaned from : '.$dir)
            ->expectsOutput('Clean up process complete')
            ->assertExitCode(0);
        Log::shouldHaveReceived('info')->with('command clean:tempfiles was run');
    }


    public function test_it_reports_error_when_directory_exists_but_not_cleaned()
    {
        $filesystem = $this->createMock(Filesystem::class);
        $dir = base_path('storage/temp');
        Config::set('maintenance-tools.temp_files', [$dir]);
        $filesystem->method('exists')->with($dir)->willReturn(true);
        $filesystem->expects($this->once())->method('cleanDirectory')->with($dir);
        $filesystem->method('files')->with($dir)->willReturn(['file1.tmp']);

        // Bind the mock to the container and register the command
        $this->app->instance(Filesystem::class, $filesystem);
        $this->app->singleton(CleanTemporaryFilesCommand::class, function () use ($filesystem) {
            return new CleanTemporaryFilesCommand($filesystem);
        });
        $this->app['Illuminate\\Contracts\\Console\\Kernel']->registerCommand($this->app->make(CleanTemporaryFilesCommand::class));

        $this->artisan('clean:tempfiles')
            ->expectsOutput('Temp files not removed from directory: '.$dir)
            ->expectsOutput('Clean up process complete')
            ->assertExitCode(1);
    }


    public function test_it_reports_error_when_directory_is_missing()
    {
        $filesystem = $this->createMock(Filesystem::class);
        $dir = base_path('storage/missing');
        Config::set('maintenance-tools.temp_files', [$dir]);
        $filesystem->method('exists')->with($dir)->willReturn(false);

        // Bind the mock to the container and register the command
        $this->app->instance(Filesystem::class, $filesystem);
        $this->app->singleton(CleanTemporaryFilesCommand::class, function () use ($filesystem) {
            return new CleanTemporaryFilesCommand($filesystem);
        });
        $this->app['Illuminate\\Contracts\\Console\\Kernel']->registerCommand($this->app->make(CleanTemporaryFilesCommand::class));

        $this->artisan('clean:tempfiles')
            ->expectsOutput('cleanup not complete: missing directory: '.$dir)
            ->expectsOutput('Clean up process complete')
            ->assertExitCode(1);
        Log::shouldHaveReceived('error')->with('cleanup not complete: missing directory: '.$dir);
    }
}
