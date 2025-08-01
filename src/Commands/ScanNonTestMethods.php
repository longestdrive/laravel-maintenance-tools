<?php

namespace Longestdrive\LaravelMaintenanceTools\Commands;

use Illuminate\Console\Command;

class ScanNonTestMethods extends Command
{
    protected $signature = 'scan:nonTestMethods';

    protected $description = 'Scan test classes for public methods not prefixed with "test" (excluding setUp) and log the results.';

    public function handle()
    {
        $logDir = storage_path('logs');
        $baseName = 'non_test_methods_';
        $ext = '.log';

        // Find the next available index
        $files = glob($logDir.'/'.$baseName.'*.log');
        $maxIndex = 0;
        foreach ($files as $file) {
            if (preg_match('/'.preg_quote($baseName, '/').'(\d+)\.log$/', $file, $matches)) {
                $idx = (int) $matches[1];
                if ($idx > $maxIndex) {
                    $maxIndex = $idx;
                }
            }
        }
        $nextIndex = $maxIndex + 1;
        $logPath = $logDir.'/'.$baseName.$nextIndex.$ext;
        $output = '';

        $directory = new \RecursiveDirectoryIterator(base_path('tests'));
        $iterator = new \RecursiveIteratorIterator($directory);
        $regex = new \RegexIterator($iterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);

        foreach ($regex as $file) {
            $filePath = $file[0];
            $content = file_get_contents($filePath);

            $tokens = token_get_all($content);
            $className = null;
            $methods = [];
            $docComment = null;

            for ($i = 0; $i < count($tokens); $i++) {
                if ($tokens[$i][0] === T_CLASS) {
                    for ($j = $i + 1; $j < count($tokens); $j++) {
                        if ($tokens[$j][0] === T_STRING) {
                            $className = $tokens[$j][1];
                            break;
                        }
                    }
                }
                if ($tokens[$i][0] === T_DOC_COMMENT) {
                    $docComment = $tokens[$i][1];
                }
                if ($tokens[$i][0] === T_FUNCTION) {
                    $fnName = $tokens[$i + 2][1] ?? null;
                    $isPublic = false;
                    for ($k = $i - 1; $k > 0; $k--) {
                        if ($tokens[$k][0] === T_PUBLIC) {
                            $isPublic = true;
                            break;
                        }
                        if (in_array($tokens[$k][0], [T_PROTECTED, T_PRIVATE, T_STATIC, T_ABSTRACT, T_FINAL, T_VAR])) {
                            break;
                        }
                        if ($tokens[$k] === '{' || $tokens[$k] === '}') {
                            break;
                        }
                    }
                    if (
                        $isPublic &&
                        $fnName &&
                        strpos($fnName, 'test') !== 0 &&
                        $fnName !== 'setUp' &&
                        $docComment &&
                        strpos($docComment, '@test') !== false
                    ) {
                        $methods[] = $fnName;
                    }
                    $docComment = null;
                }
            }

            if ($className && ! empty($methods)) {
                $output .= "Class: $className in $filePath\n";
                foreach ($methods as $method) {
                    $output .= "  - $method\n";
                }
            }
        }

        file_put_contents($logPath, $output);
        $this->info("Scan complete. Results written to: $logPath");
    }
}
