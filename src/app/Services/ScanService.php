<?php

namespace App\Services;

use App\Jobs\TraverseFolderJob;
use App\Models\Job;

class ScanService
{
    /**
     * Path to the scanner log file.
     */
    private const SCANNER_LOG = 'logs/scanner.log';

    /**
     * Public entry point for triggering the scan.
     */
    public function run(?string $overridePath = null)
    {
        if (!$this->isRunning())
        {
            $this->truncateScannerLog();
            $this->dispatchScanJob($overridePath);
        }
    }

    /**
     * Truncate the scanner.log file.
     */
    private function truncateScannerLog()
    {
        $logFile = storage_path(self::SCANNER_LOG);

        if (file_exists($logFile)) {
            $handle = fopen($logFile, 'w');
            fclose($handle);
        }
    }

    /**
     * Dispatch the folder traversal scan job.
     */
    private function dispatchScanJob(?string $overridePath)
    {
        $pathConfig = $overridePath ?? config('settings.media_root');

        if ($pathConfig && !empty($pathConfig))
        {
            $params = [
                'path'   => realpath(resource_path($pathConfig)),
                'forced' => boolval(config('settings.force_rescan')),
            ];

            TraverseFolderJob::dispatch(...$params)->onQueue('folders');
        }
    }

    /**
     * Check if any of the given jobs are currently queued or running.
     */
    public static function isRunning(): bool
    {
        return Job::picOJobs()->where(function ($q) {
            $q->where('payload', 'like', '%TraverseFolderJob%')
              ->orWhere('payload', 'like', '%ProcessPhotoJob%');
        })->exists();
    }
}
