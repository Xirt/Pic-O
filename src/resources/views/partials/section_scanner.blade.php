
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

    <div class="progress-container d-flex align-items-center job-progress bg-light rounded p-3 my-2">

        <div class="spinner position-relative ratio ratio-1x1">

            <div class="spinner-custom position-absolute top-50 start-50 bg-transparent spinner-outer"></div>
            <div class="spinner-custom position-absolute top-50 start-50 bg-transparent spinner-inner"></div>

            <div class="spinner-text position-absolute text-center d-flex justify-content-center align-items-center w-100 h-100">
                Scanning
            </div>

        </div>

        <div class="flex-grow-1 ms-5">

            <div class="job-progress position-relative my-3" data-job-type="TraverseFolderJob">
                <span class="job-count"></span> folder(s) remaining
                <i class="position-absolute end-0 fade bi bi-caret-up-fill arrow-up text-danger"></i>
                <i class="position-absolute end-0 fade bi bi-caret-down-fill arrow-down text-success"></i>
            </div>

            <div class="job-progress position-relative my-3" data-job-type="ProcessPhotoJob">
                <span class="job-count"></span> photo(s) remaining
                <i class="position-absolute end-0 fade bi bi-caret-up-fill arrow-up text-danger"></i>
                <i class="position-absolute end-0 fade bi bi-caret-down-fill arrow-down text-success"></i>
            </div>

        </div>

    </div>

</div>