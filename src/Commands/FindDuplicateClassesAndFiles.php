<?php

namespace Longestdrive\LaravelMaintenanceTools\Commands;

use Illuminate\Console\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Command to scan a specified folder for duplicate class names and file names.
 * The scan retrieves all PHP files in the folder recursively and identifies duplicates by comparing file names
 * and class names. Results are logged to a file in the storage logs directory.
 */
class FindDuplicateClassesAndFiles extends Command
{
    private const LOG_FILE_PREFIX = 'duplicate_scan_';

    private const LOG_FILE_EXT = '.log';

    private const PHP_EXT = 'php';

    protected $signature = 'scan:duplicates {folder : Folder to scan (relative to project root)}';

    protected $description = 'Scan for duplicate class names and file names in a folder recursively';

    private array $classNames = [];

    private array $fileNames = [];

    private array $duplicates = ['classes' => [], 'files' => []];

    public function handle(): int
    {
        $folder = $this->validateFolder();
        if (! $folder) {
            return 1;
        }

        $this->scanDirectory($folder);
        $this->findDuplicates();

        $output = $this->generateOutput();
        $logPath = $this->writeToLogFile($output);

        $this->info("Scan complete. Results written to: $logPath");

        return 0;
    }

    private function validateFolder(): string|false
    {
        $folder = base_path($this->argument('folder'));
        if (! is_dir($folder)) {
            $this->error("Folder not found: $folder");

            return false;
        }

        return $folder;
    }

    private function scanDirectory(string $folder): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($folder)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === self::PHP_EXT) {
                $this->processPhpFile($file);
            }
        }
    }

    private function processPhpFile(\SplFileInfo $file): void
    {
        $fileName = $file->getFilename();
        $filePath = $file->getPathname();

        $this->fileNames[$fileName][] = $filePath;

        $content = file_get_contents($filePath);
        if (preg_match('/class\s+([A-Za-z0-9_]+)/', $content, $matches)) {
            $this->classNames[$matches[1]][] = $filePath;
        }
    }

    private function findDuplicates(): void
    {
        foreach ($this->classNames as $name => $paths) {
            if (count($paths) > 1) {
                $this->duplicates['classes'][$name] = $paths;
            }
        }

        foreach ($this->fileNames as $name => $paths) {
            if (count($paths) > 1) {
                $this->duplicates['files'][$name] = $paths;
            }
        }
    }

    private function generateOutput(): string
    {
        if (empty($this->duplicates['classes']) && empty($this->duplicates['files'])) {
            return "No duplicate class names or file names found.\n";
        }

        $output = '';

        if (! empty($this->duplicates['classes'])) {
            $output .= "Duplicate class names:\n";
            $output .= $this->formatDuplicatesList($this->duplicates['classes']);
        }

        if (! empty($this->duplicates['files'])) {
            $output .= "Duplicate file names:\n";
            $output .= $this->formatDuplicatesList($this->duplicates['files']);
        }

        return $output;
    }

    private function formatDuplicatesList(array $items): string
    {
        $output = '';
        foreach ($items as $name => $paths) {
            $output .= "  $name:\n";
            foreach ($paths as $path) {
                $output .= "    - $path\n";
            }
        }

        return $output;
    }

    private function writeToLogFile(string $content): string
    {
        $logPath = $this->generateLogPath();
        file_put_contents($logPath, $content);

        return $logPath;
    }

    private function generateLogPath(): string
    {
        $logDir = storage_path('logs');
        $nextIndex = $this->getNextLogFileIndex($logDir);

        return $logDir.'/'.self::LOG_FILE_PREFIX.$nextIndex.self::LOG_FILE_EXT;
    }

    private function getNextLogFileIndex(string $logDir): int
    {
        $pattern = self::LOG_FILE_PREFIX.'*'.self::LOG_FILE_EXT;
        $files = glob($logDir.'/'.$pattern);

        $maxIndex = 0;
        foreach ($files as $file) {
            if (preg_match('/'.preg_quote(self::LOG_FILE_PREFIX, '/').'(\d+)'.preg_quote(self::LOG_FILE_EXT, '/').'$/', $file, $matches)) {
                $maxIndex = max($maxIndex, (int) $matches[1]);
            }
        }

        return $maxIndex + 1;
    }
}
