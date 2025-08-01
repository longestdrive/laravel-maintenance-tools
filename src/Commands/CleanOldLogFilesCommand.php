<?php

namespace Longestdrive\LaravelMaintenanceTools\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Log;

/**
 * Class CleanOldLogFilesCommand
 *
 * This command deletes redundant .gz log files in the storage/logs folder
 * where the creation date of the file is more than 30 days old.
 */
class CleanOldLogFilesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:clean-old {--days=30 : Number of days to keep logs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes .gz log files older than the specified number of days (default: 30)';

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
        $days = (int) $this->option('days');
        $logsPath = storage_path('logs');
        $cutoffDate = Carbon::now()->subDays($days);
        $fileExtension = config('maintenance-tools.log_file_extension', '.gz');

        $this->info("Cleaning .gz log files older than {$days} days from {$logsPath}");

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

        $this->info("Deleted {$deletedCount} old .gz log files");
        Log::info("Command logs:clean-old was run. Deleted {$deletedCount} old .gz log files");

        return 0;
    }
}
