<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

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

    private const LOG_CHANNEL = 'scanner';

    private const PHOTO_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];

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
        Log::channel(self::LOG_CHANNEL)->info("Scanning folder: $this->relativePath");

        // Add directory entry
        $folder = Folder::firstOrCreate([
            'path'      => $this->relativePath,
            'name'      => $this->folderName,
            'parent_id' => $this->parentId
        ]);

        $rootPath = realpath(resource_path(config('settings.media_root')));
        $ignorePatterns = $this->getIgnorePatterns($this->path, $rootPath);

        $this->scanSubdirectories($folder->id, $ignorePatterns);
        $this->scanFiles($folder->id, $ignorePatterns);

        Log::channel(self::LOG_CHANNEL)->info("Completed scan: $this->relativePath");
    }

    private function scanSubdirectories(int $folderId, array $ignorePatterns): void
    {
        $subfolders = collect(File::directories($this->path))
            ->filter(function ($subfolder) use ($ignorePatterns) {
                $relative = $this->getRelativePath($subfolder);
                return !$this->isIgnored($relative, $ignorePatterns);
            });

        // Scan found subdirectories
        foreach ($subfolders as $subfolder)
        {
            $relativePath = $this->getRelativePath($subfolder);

            Log::channel(self::LOG_CHANNEL)->info("Requesting folder scan: $relativePath");
            TraverseFolderJob::dispatch($subfolder, $folderId)->onQueue('folders');
        }

        // Remove obsolete (sub)directory entries
        $dbSubfolders = Folder::where('path', 'like', $this->relativePath . DIRECTORY_SEPARATOR . '%')->get();
        foreach ($dbSubfolders as $dbFolder)
        {
            $folderName   = basename($dbFolder->path);
            $absolutePath = resource_path($dbFolder->path);

            if (!File::isDirectory($absolutePath) || $this->isIgnored($dbFolder->path, $ignorePatterns))
            {
                $dbFolder->delete();
            }
        }
    }

    private function scanFiles(int $folderId, array $ignorePatterns): void
    {
        $foundFilenames = [];
        $files = collect(File::files($this->path))
            ->filter(function ($file) use ($ignorePatterns) {
                $relative = $this->getRelativePath($file->getPathname());
                return !$this->isIgnored($relative, $ignorePatterns);
            });

        // Scan found files
        $knownPhotos = $this->getKnownPhotos($folderId);
        foreach ($files as $file)
        {
            $fileName    = $file->getFileName();
            $fileAbsPath = $file->getPathname();
            $fileRelPath = $this->getRelativePath($fileAbsPath);
            $fileUpdate  = Carbon::createFromTimestamp(filemtime($fileAbsPath));

            // Skip unchanged files
            $photo = $knownPhotos->get($fileName);
            if ($photo && $fileUpdate->lte($photo->updated_at))
            {
                Log::channel(self::LOG_CHANNEL)->info("Skipping unchanged photo: $fileRelPath");
                continue;
            }

            // Process relevant files
            $extension = Str::lower($file->getExtension());
            if (in_array($extension, self::PHOTO_EXTENSIONS))
            {
                Log::channel(self::LOG_CHANNEL)->info("Requesting photo scan: $fileRelPath");
                ProcessPhotoJob::dispatch($folderId, $fileAbsPath)->onQueue('photos');

                $foundFilenames[] = $fileName;
            }
        }

        // Remove obsolete photo entries
        Photo::where('folder_id', $folderId)
            ->whereNotIn('filename', $foundFilenames)
            ->delete();
    }
    
    protected function getKnownPhotos(int $folderId): Collection
    {
        return Photo::where('folder_id', $folderId)
            ->get()
            ->keyBy('filename');
    }
    
    protected function getRelativePath(string $path): string
    {
        return Str::after($path, resource_path() . DIRECTORY_SEPARATOR);
    }

    protected function getIgnorePatterns(string $dir, string $rootDir): array
    {
        return array_unique(array_merge(
            $this->getIgnoreLines($rootDir),
            $this->getIgnoreLines($dir)
        ));
    }

    protected function getIgnoreLines(string $dir) : array
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
        $filename   = basename($path);
        $normalized = str_replace('\\', '/', $path);

        foreach ($patterns as $pattern)
        {
            if (fnmatch($pattern, $normalized) || fnmatch($pattern, $filename))
            {
                Log::channel(self::LOG_CHANNEL)->info("Ignoring path: $path");
                return true;
            }
        }
        return false;
    }
}
