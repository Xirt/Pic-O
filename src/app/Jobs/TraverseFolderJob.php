<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;

use App\Jobs\ProcessPhotoJob;
use App\Models\Folder;
use App\Models\Photo;

class TraverseFolderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $path;

    protected ?int $parentId;

    public const PHOTO_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];

    /**
     * Constructor
     */
    public function __construct(string $path, int $parentId = NULL)
    {
        $this->parentId = $parentId;
        $this->path = $path;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $absolutePath = realpath($this->path);
        $relativePath = str_replace(resource_path(), '', $absolutePath);

        $folderName = basename($absolutePath);
        if (empty($folderName))
        {
            $folderName = 'root';
        }

        // Store this folder
        $folder = Folder::firstOrCreate([
            'path'      => $relativePath,
            'name'      => $folderName,
            'parent_id' => $this->parentId
        ]);

        // Trigger scan of subdirectories
        $subfolders = File::directories($absolutePath);
        foreach ($subfolders as $subfolder)
        {
            TraverseFolderJob::dispatch($subfolder, $folder->id);
        }

        // Remove obsolete folder entries
        $dbSubfolders = Folder::where('path', 'like', $relativePath . DIRECTORY_SEPARATOR . '%')->get();
        foreach ($dbSubfolders as $dbFolder)
        {
            if (!File::isDirectory(resource_path($dbFolder->path)))
            {
                $dbFolder->delete();
            }
        }

        // Trigger scan of photos
        $foundFilenames = [];
        foreach (File::files($absolutePath) as $file)
        {
            $extension = strtolower($file->getExtension());
            if (in_array($extension, self::PHOTO_EXTENSIONS))
            {
                $foundFilenames[] = $file->getFilename();
                ProcessPhotoJob::dispatch($folder->id, $file->getPathname());
            }
        }

        // Remove obsolete photo entries
        $existingPhotos = Photo::where('folder_id', $folder->id)->get();
        Photo::where('folder_id', $folder->id)
            ->whereNotIn('filename', $foundFilenames)
            ->delete();
    }
}
