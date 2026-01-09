<?php

namespace App\Jobs;

use FilesystemIterator;
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

/**
 * Recursively scans a folder for subfolders and photo files.
 *
 * This job creates or updates Folder models for directories,
 * dispatches ProcessPhotoJob for each relevant photo file, and removes
 * obsolete database entries for deleted files or directories.
 */
class TraverseFolderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var bool Force reprocessing of files even if unchanged */
    protected bool $forced;

    /** @var string Absolute filesystem path of the folder */
    protected string $path;

     /** @var int|null Parent folder database ID */
    protected ?int $parentId;

     /** @var string Name of the folder */
    protected string $folderName;

    /** @var string Relative (resource) path of the folder */
    protected string $relativePath;

    /** @var string Scanner log channel (for information logging) */
    private const LOG_CHANNEL = 'scanner';

    /**
    * Allowed photo file extensions for processing.
    *
    * @var array<int, string>
    */
    private array $photoExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    /**
     * @param string   $path     Absolute folder path
     * @param int|null $parentId Parent folder ID
     * @param bool     $forced   Force reprocessing of files
     */
    public function __construct(string $path, int $parentId = NULL, $forced = false)
    {
        $this->parentId     = $parentId;
        $this->path         = $path;
        $this->forced       = $forced;

        $this->folderName   = basename($path);
        $this->relativePath = str_replace(resource_path(), '', $path);

        if ($this->checkHEICSupport()) {
            array_push($this->photoExtensions, 'heic', 'heif');
        }

    }

    /**
     * Execute the folder traversal job.
     *
     * @return void
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

    /**
     * Scan and dispatch jobs for subdirectories.
     *
     * @param int                $folderId
     * @param array<int, string> $ignorePatterns
     *
     * @return void
     */
    private function scanSubdirectories(int $folderId, array $ignorePatterns): void
    {
        $subfolders = collect(File::directories($this->path))
            ->filter(function ($subfolder) use ($ignorePatterns) {

                if (@is_link($subfolder))
                {
                    Log::channel(self::LOG_CHANNEL)->info("Skipping symlink: $subfolder");
                    return false;
                }

                $relative = $this->getRelativePath($subfolder);
                return !$this->isIgnored($relative, $ignorePatterns);
            });

        // Scan found subdirectories
        foreach ($subfolders as $subfolder)
        {
            $relativePath = $this->getRelativePath($subfolder);

            Log::channel(self::LOG_CHANNEL)->info("Requesting folder scan: $relativePath");
            TraverseFolderJob::dispatch($subfolder, $folderId, $this->forced)->onQueue('folders');
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

    /**
     * Scan files in the current directory and dispatch photo jobs.
     *
     * @param int                $folderId
     * @param array<int, string> $ignorePatterns
     *
     * @return void
     */
    private function scanFiles(int $folderId, array $ignorePatterns): void
    {
        $files = [];
        $existingPhotos = $this->getExistingPhotos($folderId);

        // Retrieve relevant files
        $iterator = new FilesystemIterator($this->path, FilesystemIterator::SKIP_DOTS);
        foreach ($iterator as $fileInfo)
        {
            if ($fileInfo->isFile())
            {
                $ext = Str::lower($fileInfo->getExtension());
                if (in_array($ext, $this->photoExtensions, true)) {
                    $files[] = $fileInfo->getPathname();
                }
            }
        }

        // Check file processing needs
        foreach ($files as $fileAbsPath)
        {
            $fileRelPath = $this->getRelativePath($fileAbsPath);
            if ($this->isIgnored($fileRelPath, $ignorePatterns))
            {
                continue;
            }

            $fileName = basename($fileAbsPath);
            if ($photo = $existingPhotos->get($fileName))
            {
                unset($existingPhotos[$fileName]);

                if (!$this->forced)
                {
                    $fileTime = @filemtime($fileAbsPath);
                    $fileUpdate = Carbon::createFromTimestamp($fileTime);

                    if ($fileTime !== false && $fileUpdate->lte($photo->updated_at))
                    {
                        Log::channel(self::LOG_CHANNEL)->info("Skipping unchanged photo: $fileRelPath");
                        continue;
                    }
                }
            }

            // Process file (in separate job)
            Log::channel(self::LOG_CHANNEL)->info("Requesting photo scan: $fileRelPath");
            ProcessPhotoJob::dispatch($folderId, $fileAbsPath)->onQueue('photos');
        }

        // Remove obsolete photo entries
        if ($existingPhotos->isNotEmpty())
        {
            Photo::destroy($existingPhotos->pluck('id'));
        }
    }

    /**
     * Get existing photos indexed by filename for the given Folder ID.
     *
     * @param int $folderId
     *
     * @return Collection<string, Photo>
     */
    protected function getExistingPhotos(int $folderId): Collection
    {
        return Photo::where('folder_id', $folderId)
            ->get()
            ->keyBy('filename');
    }

    /**
     * Convert absolute path to resource-relative path.
     *
     * @param string $path
     *
     * @return string
     */
    protected function getRelativePath(string $path): string
    {
        return Str::after($path, resource_path() . DIRECTORY_SEPARATOR);
    }

    /**
     * Retrieve ignore patterns from root and local directories.
     *
     * @param string $dir
     * @param string $rootDir
     *
     * @return array<int, string>
     */
    protected function getIgnorePatterns(string $dir, string $rootDir): array
    {
        return array_unique(array_merge(
            $this->getIgnoreLines($rootDir),
            $this->getIgnoreLines($dir)
        ));
    }

    /**
     * Read ignore rules from `.ignore` file in given directory.
     *
     * @param string $dir
     *
     * @return array<int, string>
     */
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

    /**
     * Determine whether given path matches (one of the) provided ignore patterns.
     *
     * @param string              $path
     * @param array<int, string>  $patterns
     *
     * @return bool
     */
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
    }   protected static ?bool $supportsHeic = null;

    /**
     * Checks whether the current environment can handle HEIC files
     *
     * @return bool
     */
    protected static function checkHEICSupport(): bool
    {
        if (extension_loaded('imagick'))
        {
            try
            {
                $formats = array_map('strtoupper', \Imagick::queryFormats());
                return in_array('HEIC', $formats, true) || in_array('HEIF', $formats, true);
            } catch (\Throwable) {}
        }

        return false;
    }

}
