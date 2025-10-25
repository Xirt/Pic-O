@extends('layouts.app')

@section('title', 'Gallery')

@section('content')

    <div id="currentSectionLabel" class="rounded-start fixed-top bg-dark text-light px-3 py-2 fw-bold" style="
        top: 80px;
        right: 0px;
        left: auto;
        width: fit-content;
        user-select: none;
        pointer-events: none;">

        <div class="spinner-border spinner-border-sm" role="status">
            <span class="visually-hidden">...</span>
        </div>

    </div>

    <div class="container-lg position-relative px-1 px-md-4 mt-3" id="container">
        <nav id="dummy-nav" class="d-none"></nav>
    </div>

    <div id="picoView" class="picoview d-none d-flex justify-content-center align-items-center position-fixed top-0 start-0 w-100 h-100 bg-white" aria-hidden="true">

        <div class="bg-light position-absolute top-0 end-0 m-2 p-2 rounded" style="z-index:2; --bs-bg-opacity: .5;">

            <button id="slideshowBtn" class="btn btn-secondary me-1" title="Toggle slideshow">
                <i class="bi bi-play-fill"></i>
                <i class="bi bi-stop-fill"></i>
            </button>

            <button id="closeBtn" class="btn btn-secondary" title="Close">
                <i class="bi bi-x-lg"></i>
            </button>

        </div>

        <button id="prevBtn" class="btn btn-secondary btn-swipe slideshow-hide position-absolute top-50 start-0 translate-middle-y m-3" style="z-index:2;"><i class="bi bi-chevron-left"></i></button>

        <div id="picoViewInner" class="picoview-inner w-100 h-100 position-relative overflow-hidden">

            <div class="spinner-overlay d-flex align-items-center justify-content-center d-none" id="spinner">
                <div class="spinner-border text-secondary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>

        <button id="nextBtn" class="btn btn-secondary btn-swipe slideshow-hide position-absolute top-50 end-0 translate-middle-y m-3" style="z-index:2;"><i class="bi bi-chevron-right"></i></button>

        <div id="toolbar" class="toolbar slideshow-hide bg-light position-absolute bottom-0 start-50 translate-middle-x d-flex gap-2 m-2 p-2 rounded" style="z-index:2; --bs-bg-opacity: .5;">

            <button class="btn btn-secondary action" data-action="photo.info" title="Details">
                <i class="bi bi-info-circle-fill"></i>
            </button>

            <button class="btn btn-secondary action" data-action="photo.download" title="Download">
                <i class="bi bi-download"></i>
            </button>

        </div>

    </div>

    @include('partials.photo-info-form')

@endsection

@push('scripts')
@vite('resources/js/timeline.js')
@endpush
