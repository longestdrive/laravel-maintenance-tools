<?php

namespace Longestdrive\LaravelMaintenanceTools\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Log;

/**
 * Class CleanOldLogFilesCommand
 *
 * This command deletes archived log files (matching the configured
 * `log_file_extension`, default .gz) in the storage/logs folder that are older
 * than the configured `log_retention_days` (default 30) or the --days option.
 */
class CleanOldLogFilesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clean:logs {--days= : Number of days to keep logs (defaults to the log_retention_days config value)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes archived log files older than the configured retention period';

    /**
     * The filesystem instance.
     */
    private Filesystem $filesystem;

    /**
     * Create a new command instance.
     */
    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();
        $this->filesystem = $filesystem;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $days = (int) ($this->option('days') ?? config('maintenance-tools.log_retention_days', 30));
        $logsPath = storage_path('logs');
        $cutoffDate = Carbon::now()->subDays($days);
        $fileExtension = config('maintenance-tools.log_file_extension', '.gz');

        $this->info("Cleaning {$fileExtension} log files older than {$days} days from {$logsPath}");

        // Check if the logs directory exists
        if (! $this->filesystem->exists($logsPath)) {
            $this->error("Logs directory does not exist: {$logsPath}");
            Log::error("Logs directory does not exist: {$logsPath}");

            return 1;
        }

        // Get all files with the specified extension in the logs directory
        $files = $this->filesystem->glob("{$logsPath}/*".$fileExtension);

        if (empty($files)) {
            $this->info("No {$fileExtension} log files found in {$logsPath}");

            return 0;
        }

        $deletedCount = 0;

        foreach ($files as $file) {
            $fileCreationTime = $this->filesystem->lastModified($file);
            $fileCreationDate = Carbon::createFromTimestamp($fileCreationTime);

            if ($fileCreationDate->lt($cutoffDate)) {
                $this->filesystem->delete($file);
                $deletedCount++;
                $this->line('Deleted: '.basename($file)." (Created: {$fileCreationDate->format('Y-m-d')})");
            }
        }

        $this->info("Deleted {$deletedCount} old {$fileExtension} log files");
        Log::info("Command clean:logs was run. Deleted {$deletedCount} old {$fileExtension} log files");

        return 0;
    }
}
