
<p>To keep your photo library up to date, the scanner checks for new or changed files across all folders. Click "Scan Now" to manually start a scan. This will index all photos and folders, ensuring your gallery reflects the latest content.</p>
<p>During a scan, you can monitor the progress on this page.</p>


<div class="mx-lg-5">

    <div class="text-end my-2 mb-3">
        <button type="button" class="btn btn-sm btn-primary mx-2" id="scanButton">
            <i class="bi bi-pencil me-1"></i> Scan now
        </button>

        <a href="{{ route('admin.log') }}" class="btn btn-sm btn-secondary" role="button">
            <i class="bi bi-download me-1"></i> Log
        </a>
    </div>

    <div class="progress-container">

        <div class="d-flex align-items-center job-progress gap-2 my-2" data-job-type="TraverseFolderJob">

            <div class="job-label">
                Folders
            </div>

            <div class="flex-grow-1">
                <div class="progress">
                    <div class="progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100">
                        <span class="job-count"></span>
                    </div>
                </div>
            </div>

        </div>

        <div class="d-flex align-items-center job-progress gap-2 my-2" data-job-type="ProcessPhotoJob">

            <div class="job-label">
                Photos
            </div>

            <div class="flex-grow-1">
                <div class="progress">
                    <div class="progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100">
                        <span class="job-count"></span>
                    </div>
                </div>
            </div>

        </div>

    </div>


</div>