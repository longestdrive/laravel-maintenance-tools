<?php

declare(strict_types=1);

namespace Longestdrive\LaravelMaintenanceTools\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase;

class RepairMigrationTableCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Register the command
        $this->app->singleton(
            \Longestdrive\LaravelMaintenanceTools\Commands\RepairMigrationTableCommand::class,
            fn ($app) => new \Longestdrive\LaravelMaintenanceTools\Commands\RepairMigrationTableCommand
        );

        $this->app['Illuminate\\Contracts\\Console\\Kernel']->registerCommand(
            $this->app->make(\Longestdrive\LaravelMaintenanceTools\Commands\RepairMigrationTableCommand::class)
        );

        // Ensure migrations table exists
        if (! Schema::hasTable('migrations')) {
            Schema::create('migrations', function ($table) {
                $table->increments('id');
                $table->string('migration');
                $table->integer('batch');
            });
        }
    }

    /** @test */
    public function it_inserts_missing_migrations()
    {
        // Create a test migrations directory and files
        $migrationsPath = database_path('migrations');
        if (! File::exists($migrationsPath)) {
            File::makeDirectory($migrationsPath, 0755, true);
        }

        $testMigrations = [
            '2023_01_01_000000_create_users_table.php',
            '2023_01_02_000000_create_posts_table.php',
        ];

        foreach ($testMigrations as $migration) {
            File::put(
                database_path("migrations/{$migration}"),
                '<?php return new class extends Migration {};'
            );
        }

        // Simulate that only one migration has been recorded
        \DB::table('migrations')->insert([
            'migration' => '2023_01_01_000000_create_users_table',
            'batch' => 1,
        ]);

        // Run the repair command
        $this->artisan('migration:repair')->assertExitCode(0);

        // Assert the missing migration was added
        $this->assertDatabaseHas('migrations', [
            'migration' => '2023_01_02_000000_create_posts_table',
            'batch' => 2,
        ]);

        // Cleanup test migration files
        foreach ($testMigrations as $migration) {
            File::delete(database_path("migrations/{$migration}"));
        }
    }

    protected function tearDown(): void
    {
        // Clean up the migrations directory if it was created during tests
        if (File::exists(database_path('migrations'))) {
            File::deleteDirectory(database_path('migrations'));
        }

        parent::tearDown();
    }
}
