<?php

namespace App\Services;

use App\Jobs\TraverseFolderJob;
use App\Models\Job;

/**
 * Service responsible for scanning media folders and processing photos.
 *
 * Provides methods to start folder traversal jobs, check if scans are
 * currently running, and manage scanner logs. Dispatches TraverseFolderJob
 * for recursive folder scanning and ProcessPhotoJob for individual photos.
 */
class ScanService
{
    /**
     * Path to the scanner log file.
     */
    private const SCANNER_LOG = 'logs/scanner.log';

    /**
     * Public entry point for triggering the scan.
     *
     * @param string|null $overridePath Optional path to scan instead of default media_root
     */
    public function run(?string $overridePath = null): void
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
    private function truncateScannerLog(): void
    {
        $logFile = storage_path(self::SCANNER_LOG);

        if (file_exists($logFile)) {
            $handle = fopen($logFile, 'w');
            fclose($handle);
        }
    }

    /**
     * Dispatch the folder traversal scan job.
     *
     * @param string|null $overridePath Optional path to scan
     */
    private function dispatchScanJob(?string $overridePath): void
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
     * Check if any of the relevant jobs are currently queued or running.
     *
     * @return bool True if a TraverseFolderJob or ProcessPhotoJob is queued or running
     */
    public static function isRunning(): bool
    {
        return Job::picOJobs()->where(function ($q) {
            $q->where('payload', 'like', '%TraverseFolderJob%')
              ->orWhere('payload', 'like', '%ProcessPhotoJob%');
        })->exists();
    }
}
