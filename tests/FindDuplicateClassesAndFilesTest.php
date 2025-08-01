<?php

declare(strict_types=1);

namespace Longestdrive\LaravelMaintenanceTools\Tests;

use Illuminate\Filesystem\Filesystem;

class FindDuplicateClassesAndFilesTest extends TestCase
{
    private string $testDir;

    protected function setUp(): void
    {
        parent::setUp();
        // Register the command so output is captured
        $this->app->singleton(\Longestdrive\LaravelMaintenanceTools\Commands\FindDuplicateClassesAndFiles::class, function ($app) {
            return new \Longestdrive\LaravelMaintenanceTools\Commands\FindDuplicateClassesAndFiles;
        });
        $this->app['Illuminate\\Contracts\\Console\\Kernel']->registerCommand(
            $this->app->make(\Longestdrive\LaravelMaintenanceTools\Commands\FindDuplicateClassesAndFiles::class)
        );
        $this->testDir = base_path('tests/tmp_duplicates');
        if (! is_dir($this->testDir)) {
            mkdir($this->testDir, 0777, true);
        }
        // Clean up old log files
        $logs = glob(storage_path('logs/duplicate_scan_*.log'));
        foreach ($logs as $log) {
            unlink($log);
        }
    }

    protected function tearDown(): void
    {
        $fs = new Filesystem;
        if (is_dir($this->testDir)) {
            $fs->deleteDirectory($this->testDir);
        }
        $logs = glob(storage_path('logs/duplicate_scan_*.log'));
        foreach ($logs as $log) {
            unlink($log);
        }
        parent::tearDown();
    }

    /** @test */
    public function it_reports_no_duplicates()
    {
        file_put_contents($this->testDir.'/A.php', "<?php\nclass A {}");
        file_put_contents($this->testDir.'/B.php', "<?php\nclass B {}");

        $this->artisan('scan:duplicates', ['folder' => 'tests/tmp_duplicates'])
            ->expectsOutput('Scan complete. Results written to: '.storage_path('logs/duplicate_scan_1.log'))

            ->assertExitCode(0);

        $logs = glob(storage_path('logs/duplicate_scan_*.log'));
        $this->assertNotEmpty($logs);
        $log = file_get_contents($logs[0]);
        $this->assertStringContainsString('No duplicate class names or file names found.', $log);
    }

    /** @test */
    public function it_detects_duplicate_class_names()
    {
        file_put_contents($this->testDir.'/A.php', "<?php\nclass Foo {}");
        file_put_contents($this->testDir.'/B.php', "<?php\nclass Foo {}");

        $this->artisan('scan:duplicates', ['folder' => 'tests/tmp_duplicates'])
            ->expectsOutput('Scan complete. Results written to: '.storage_path('logs/duplicate_scan_1.log'))
            ->assertExitCode(0);

        $logs = glob(storage_path('logs/duplicate_scan_*.log'));
        $log = file_get_contents($logs[0]);
        $this->assertStringContainsString('Duplicate class names:', $log);
        $this->assertStringContainsString('Foo:', $log);
        $this->assertStringContainsString('A.php', $log);
        $this->assertStringContainsString('B.php', $log);
    }

    /** @test */
    public function it_detects_duplicate_file_names()
    {
        mkdir($this->testDir.'/sub', 0777, true);
        file_put_contents($this->testDir.'/A.php', "<?php\nclass A {}");
        file_put_contents($this->testDir.'/sub/A.php', "<?php\nclass B {}");

        $this->artisan('scan:duplicates', ['folder' => 'tests/tmp_duplicates'])
            ->expectsOutput('Scan complete. Results written to: '.storage_path('logs/duplicate_scan_1.log'))

            ->assertExitCode(0);

        $logs = glob(storage_path('logs/duplicate_scan_*.log'));
        $log = file_get_contents($logs[0]);
        $this->assertStringContainsString('Duplicate file names:', $log);
        $this->assertStringContainsString('A.php:', $log);
        $this->assertStringContainsString('sub/A.php', $log);
    }

    /** @test */
    public function it_reports_error_for_missing_directory()
    {
        $missing = 'tests/does_not_exist';
        $this->artisan('scan:duplicates', ['folder' => $missing])
            ->expectsOutput('Folder not found: '.base_path($missing))
            ->assertExitCode(1);
    }
}
