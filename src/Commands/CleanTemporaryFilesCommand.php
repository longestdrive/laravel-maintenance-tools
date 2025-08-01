<?php

namespace Longestdrive\LaravelMaintenanceTools\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Log;

/**
 * Class CleanTemporaryFilesCommand
 *
 * This command is responsible for cleaning the application's temporary folders
 * by removing files from the directories specified in the application's configuration.
 */
class CleanTemporaryFilesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clean:tempfiles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'removes files from the apps temporary folders';

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
     */
    public function handle(): mixed
    {
        $status = 0; // Initialize status to 0 (success)

        foreach (config()->get('maintenance-tools.temp_files') as $dir) {
            // Check if the directory exists.
            if ($this->filesystem->exists($dir)) {
                // clean directory
                $this->filesystem->cleanDirectory($dir);
                // Get all files in this directory.
                $files = $this->filesystem->files($dir);
                // show error if not empty.
                if (! empty($files)) {
                    $this->error('Temp files not removed from directory: '.$dir);
                    Log::error('Temp files not removed from directory: '.$dir);
                    $status = 1; // Set status to 1 (failure)
                } else {
                    $this->info('Temp files cleaned from : '.$dir);
                }
            } else {
                //                throw new ConsoleException('clean up directory missing');
                $this->error('cleanup not complete: missing directory: '.$dir);
                Log::error('cleanup not complete: missing directory: '.$dir);
                $status = 1; // Set $status to 1 (failure)
            }
        }
        $this->info('Clean up process complete');
        Log::info('command clean:tempfiles was run');

        // Return 0 to indicate success.
        return $status;
    }
}
