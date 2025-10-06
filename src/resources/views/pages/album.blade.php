@extends('layouts.app')

@section('title', 'Album: ' . $album->name)

@section('content')

    <div class="container-lg px-4" data-album-id="{{ $album->id }}" id="album">

        <div class="d-flex align-items-center justify-content-between mb-2 ms-1">

            <h1>

                <a href="{{ route("albums.index") }}" class="btn btn-secondary m-1 mt-0 p-1 ps-2" style="aspect-ratio: 1 / 1;" aria-label="Back to albums">
                    <i class="bi bi-caret-left-fill"></i>
                </a>

                {{ $album->name }}

            </h1>

            <div class="toolbar text-end pe-1">

                <button type="button" class="btn btn-sm btn-primary select-start">
                    <i class="bi bi-check2-all"></i> Selection Mode
                </button>

                <button type="button" class="btn btn-sm btn-primary select-action mx-1" id="btn-remove-selection2">
                    <i class="bi bi-trash3-fill"></i> Remove
                </button>

                <button type="button" class="btn btn-sm btn-secondary select-stop">
                    <i class="bi bi-x-lg"></i> Cancel
                </button>

            </div>

        </div>

        <div id="grid" class="grid row w-100 g-0">
           <div class="grid-sizer"></div>
           <div class="gutter-sizer"></div>
            <div class="message-empty p-1">There are currently no items to display.</div>
        </div>

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

        <button id="prevBtn" class="btn btn-secondary position-absolute top-50 start-0 translate-middle-y m-3" style="z-index:2;"><i class="bi bi-chevron-left"></i></button>

        <div id="picoViewInner" class="picoview-inner w-100 h-100 position-relative overflow-hidden">

            <div class="spinner-overlay d-flex align-items-center justify-content-center d-none" id="spinner">
                <div class="spinner-border text-secondary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>

        <button id="nextBtn" class="btn btn-secondary position-absolute top-50 end-0 translate-middle-y m-3" style="z-index:2;"><i class="bi bi-chevron-right"></i></button>

        <div id="toolbar" class="toolbar bg-light position-absolute bottom-0 start-50 translate-middle-x d-flex gap-2 m-2 p-2 rounded" style="z-index:2; --bs-bg-opacity: .5;">

            <button class="btn btn-secondary action" data-action="photo.info" title="Details">
                <i class="bi bi-info-circle-fill"></i>
            </button>

            <button class="btn btn-secondary action" data-action="photo.download" title="Download">
                <i class="bi bi-download"></i>
            </button>

            <button class="btn btn-secondary action" data-action="photo.cover" title="Album cover">
                <i class="bi bi-star-fill"></i>
            </button>

            <button class="btn btn-secondary action" data-action="photo.remove" title="Remove">
                <i class="bi bi-trash3-fill"></i>
            </button>

        </div>
    </div>

    @include('partials.photo-info-form')

    <div class="position-fixed bottom-0 start-50 translate-middle-x p-3 mb-5" style="z-index: 110000000;">

        <div id="coverToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">

            <div class="toast-body text-center">
                The photo has been set as cover photo.
            </div>

        </div>

    </div>

    <div class="position-fixed bottom-0 start-50 translate-middle-x p-3 mb-5" style="z-index: 110000000;">

        <div id="removalToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">

        <div class="toast-header">

            <strong class="me-auto">Undo</strong>
            <small id="undo-count">1</small>
            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>

        </div>

            <div class="toast-body">

                The photo has been removed from this album.

                <div class="mt-2 border-top text-center">

                    <button type="button" class="btn btn-sm btn-secondary mt-3 mb-2 w-50" id="btn-undo">
                        <i class="bi bi-arrow-counterclockwise"></i> Undo
                    </button>

                </div>

            </div>

        </div>

    </div>

@endsection

@push('scripts')
@vite('resources/js/album.js')
@endpush
