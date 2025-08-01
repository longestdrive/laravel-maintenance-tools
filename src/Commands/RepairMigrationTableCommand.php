<?php

namespace Longestdrive\LaravelMaintenanceTools\Commands;

use DB;
use Illuminate\Console\Command;

/**
 * Class UpdateMigrationTableCommand
 *
 * The command updates/repairs the migration table with all of the migrations in the migrations folder.
 */
class RepairMigrationTableCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migration:repair';

    /**
     * Updates/repairs migration table with all of
     * the migrations in the migrations folder.
     *
     * @var string
     */
    protected $description = 'Repair migration table with migrations that have run';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    private const DIRECTORY_ENTRIES = ['.', '..'];

    public function handle(): int
    {
        $migrationFiles = $this->getMigrationFiles();
        $latestBatch = $this->getLatestMigrationBatch();

        collect($migrationFiles)
            ->filter(fn (string $file) => ! in_array($file, self::DIRECTORY_ENTRIES))
            ->each(fn (string $file) => $this->processMigrationFile($file, $latestBatch));

        return 0;
    }

    private function getMigrationFiles(): array
    {
        return scandir(database_path('migrations'));
    }

    private function getLatestMigrationBatch(): object
    {
        return DB::table('migrations')
            ->orderBy('batch', 'desc')
            ->first();
    }

    private function processMigrationFile(string $filename, object $latestBatch): void
    {
        $migrationName = basename($filename, '.php');

        $existingMigration = DB::table('migrations')
            ->where('migration', '=', $migrationName)
            ->first();

        if (! $existingMigration) {
            $this->insertMigration($migrationName, $latestBatch->batch + 1);
        }
    }

    private function insertMigration(string $migrationName, int $batchNumber): void
    {
        DB::table('migrations')->insert([
            'migration' => $migrationName,
            'batch' => $batchNumber,
        ]);
    }
}
