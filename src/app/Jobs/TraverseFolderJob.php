<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

use App\Jobs\ProcessPhotoJob;
use App\Models\Folder;
use App\Models\Photo;

class TraverseFolderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $path;

    protected ?int $parentId;

    protected string $folderName;

    protected string $relativePath;

    public const PHOTO_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];

    /**
     * Constructor
     */
    public function __construct(string $path, int $parentId = NULL)
    {
        $this->parentId     = $parentId;
        $this->path         = $path;

        $this->folderName   = basename($path);
        $this->relativePath = str_replace(resource_path(), '', $path);
    }

    /**
     * Execute the job
     */
    public function handle(): void
    {
        Log::channel('scanner')->info("Scanning folder: $this->relativePath");

        // Add directory entry
        $folder = Folder::firstOrCreate([
            'path'      => $this->relativePath,
            'name'      => $this->folderName,
            'parent_id' => $this->parentId
        ]);

        $rootPath = realpath(resource_path(config('settings.media_root')));
        $ignorePatterns = $this->getIgnorePatterns($this->path, $rootPath);

        $this->scanSubdirectories($this->path, $this->relativePath, $folder->id, $ignorePatterns);
        $this->scanFiles($this->path, $folder->id, $ignorePatterns);

        Log::channel('scanner')->info("Completed scan: $this->relativePath");
    }

    private function scanSubdirectories(string $absolutePath, string $relativePath, int $folderId, array $ignorePatterns): void
    {
        $subfolders = collect(File::directories($absolutePath))
            ->filter(function ($subfolder) use ($ignorePatterns) {
                $relative = str_replace(resource_path() . DIRECTORY_SEPARATOR, '', $subfolder);
                return !$this->isIgnored($relative, $ignorePatterns);
            });

        // Scan found subdirectories
        foreach ($subfolders as $subfolder)
        {
            $relativePath = str_replace(resource_path() . DIRECTORY_SEPARATOR, '', $subfolder);

            Log::channel('scanner')->info("Requesting folder scan: $relativePath");
            TraverseFolderJob::dispatch($subfolder, $folderId)->onQueue('folders');
        }

        // Remove obsolete (sub)directory entries
        $dbSubfolders = Folder::where('path', 'like', $relativePath . DIRECTORY_SEPARATOR . '%')->get();
        foreach ($dbSubfolders as $dbFolder)
        {
            $folderName   = basename($dbFolder->path);
            $absolutePath = resource_path($dbFolder->path);

            if (!File::isDirectory($absolutePath) || $this->isIgnored($folderName, $ignorePatterns))
            {
                $dbFolder->delete();
            }
        }
    }

    private function scanFiles(string $absolutePath, int $folderId, array $ignorePatterns): void
    {
        $foundFilenames = [];
        $files = collect(File::files($absolutePath))
            ->filter(function ($file) use ($ignorePatterns) {
                $relative = str_replace(resource_path() . DIRECTORY_SEPARATOR, '', $file->getPathname());
                return !$this->isIgnored($relative, $ignorePatterns);
            });

        // Scan found files
        foreach ($files as $file)
        {
            $extension = strtolower($file->getExtension());
            if (in_array($extension, self::PHOTO_EXTENSIONS))
            {
                $foundFilenames[] = $file->getFilename();
                $relativeFile = str_replace(resource_path() . DIRECTORY_SEPARATOR, '', $file->getPathname());

                Log::channel('scanner')->info("Requesting photo scan: $relativeFile");
                ProcessPhotoJob::dispatch($folderId, $file->getPathname())->onQueue('photos');
            }
        }

        // Remove obsolete photo entries
        Photo::where('folder_id', $folderId)
            ->whereNotIn('filename', $foundFilenames)
            ->delete();
    }

    protected function getIgnorePatterns(string $dir, string $rootDir): array
    {
        return array_unique(array_merge(
            $this->getIgnoreLines($rootDir) ?? [],
            $this->getIgnoreLines($dir) ?? []
        ));
    }

    protected function getIgnoreLines(String $dir) : array
    {
        $ignoreFile = $dir . DIRECTORY_SEPARATOR . '.ignore';

        if (File::exists($ignoreFile))
        {
            return File::lines($ignoreFile)
                ->map(fn($line) => trim($line))
                ->filter(fn($line) => $line !== '' && !str_starts_with($line, '#'))
                ->all();
        }

        return [];
    }

    protected function isIgnored(string $path, array $patterns): bool
    {
        $normalized = str_replace('\\', '/', $path);

        foreach ($patterns as $pattern)
        {
            if (fnmatch($pattern, $normalized))
            {
                Log::channel('scanner')->info("Ignoring file: $path");
                return true;
            }
        }
        return false;
    }
}
