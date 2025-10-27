<div class="picoview d-none d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100 bg-black" aria-hidden="true" id="picoView">

    <div class="picoview-stage w-100 h-100 position-relative overflow-hidden">

        <div class="spinner-overlay d-flex align-items-center justify-content-center" id="spinner">

            <div class="spinner position-relative ratio ratio-1x1">

                <div class="spinner-custom position-absolute top-50 start-50 bg-transparent spinner-outer"></div>
                <div class="spinner-custom position-absolute top-50 start-50 bg-transparent spinner-inner"></div>

                <div class="spinner-text text-light position-absolute text-center d-flex justify-content-center align-items-center w-100 h-100">
                    Loading
                </div>

            </div>

        </div>

    </div>

    <div class="bg-light position-absolute top-0 end-0 m-2 p-2 rounded" style="z-index:2; --bs-bg-opacity: .5;">

        <button id="slideshowBtn" class="btn btn-secondary me-1" title="Toggle slideshow">
            <i class="bi bi-play-fill"></i>
            <i class="bi bi-stop-fill"></i>
        </button>

        <button id="closeBtn" class="btn btn-secondary" title="Close">
            <i class="bi bi-x-lg"></i>
        </button>

    </div>

    <button id="prevBtn" class="btn btn-secondary btn-swipe slideshow-hide position-absolute top-50 start-0 translate-middle-y m-3" style="z-index:2;">
        <i class="bi bi-chevron-left"></i>
    </button>

    <button id="nextBtn" class="btn btn-secondary btn-swipe slideshow-hide position-absolute top-50 end-0 translate-middle-y m-3" style="z-index:2;">
        <i class="bi bi-chevron-right"></i>
    </button>

    <div id="toolbar" class="toolbar slideshow-hide bg-light position-absolute bottom-0 start-50 translate-middle-x d-flex gap-2 m-2 p-2 rounded" style="z-index:2; --bs-bg-opacity: .5;">

        @if($showInfoButton ?? false)
        <button class="btn btn-secondary action" data-action="photo.info" title="Show details">
            <i class="bi bi-info-circle-fill"></i>
        </button>
        @endif

        @if($showCoverButton ?? false)
        <button class="btn btn-secondary action" data-action="photo.cover" title="Set as album cover">
            <i class="bi bi-star-fill"></i>
        </button>
        @endif

        @if($showDownloadButton ?? false)
        <button class="btn btn-secondary action" data-action="photo.download" title="Download original">
            <i class="bi bi-download"></i>
        </button>
        @endif

        @if($showRemoveButton ?? false)
        <button class="btn btn-secondary action" data-action="photo.remove" title="Remove from album">
            <i class="bi bi-trash3"></i>
        </button>
        @endif

    </div>

</div>