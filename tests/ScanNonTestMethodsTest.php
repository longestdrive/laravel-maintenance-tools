<?php

namespace Longestdrive\LaravelMaintenanceTools\Tests;

use Longestdrive\LaravelMaintenanceTools\Commands\ScanNonTestMethods;

class ScanNonTestMethodsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Ensure logs directory exists
        if (! is_dir(storage_path('logs'))) {
            mkdir(storage_path('logs'), 0777, true);
        }
    }

    protected function tearDown(): void
    {
        // Clean up log files
        $files = glob(storage_path('logs/non_test_methods_*.log'));
        foreach ($files as $file) {
            unlink($file);
        }
        parent::tearDown();
    }

    /** @test */
    public function it_logs_non_test_methods_with_at_test_docblock()
    {
        $testDir = base_path('tests/ScanNonTestMethodsTestTmp');
        if (! is_dir($testDir)) {
            mkdir($testDir, 0777, true);
        }
        $testFile = $testDir.'/ExampleTest.php';
        file_put_contents($testFile, <<<'PHP'
<?php
class ExampleTest {
    /** @test */
    public function helperMethod() {}
    public function testSomething() {}
    public function setUp() {}
}
PHP
        );
        // Run the command
        $this->artisan(ScanNonTestMethods::class)
            ->expectsOutputToContain('Scan complete. Results written to:')
            ->assertExitCode(0);

        // Check log file
        $files = glob(storage_path('logs/non_test_methods_*.log'));
        $this->assertNotEmpty($files);
        $log = file_get_contents($files[0]);
        $this->assertStringContainsString('Class: ExampleTest', $log);
        $this->assertStringContainsString('helperMethod', $log);
        $this->assertStringNotContainsString('testSomething', $log);
        $this->assertStringNotContainsString('setUp', $log);

        // Clean up
        unlink($testFile);
        rmdir($testDir);
    }
}
