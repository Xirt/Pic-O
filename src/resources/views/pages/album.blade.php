@extends('layouts.app')

@section('meta')
    <!-- Open Graph -->
    <meta property="og:title" content="{{ $album->name }}">
    <meta property="og:image" content="{{ route('albums.preview', ['album' => $album->id, 'token' => request('token')->token ?? '' ]) }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $album->name }}">
    <meta name="twitter:image" content="{{ route('albums.preview', ['album' => $album->id, 'token' => request('token')->token ?? '' ]) }}">
@endsection

@section('title', 'Album: ' . $album->name)

@section('content')

    <div class="container-lg px-2 px-md-4 mt-3" data-album-id="{{ $album->id }}" id="album">

        <div class="d-flex align-items-center justify-content-between mb-2 ms-1">

            <a href="{{ route("albums.index") }}" class="text-start btn btn-sm btn-secondary no-share" aria-label="Back to albums">
                <i class="bi bi-caret-left-fill"></i>
            </a>

            <h1 class="flex-grow-1 text-truncate m-0 px-2 pb-1">

                {{ $album->name }}

            </h1>

            <div class="toolbar flex-shrink-0 text-end pe-1 no-share">

                <button type="button" class="btn btn-sm btn-primary me-1 no-select" id="shareButton" data-bs-toggle="offcanvas" data-bs-target="#offcanvas-share-album" aria-controls="offcanvas-share-album">
                    <i class="bi bi-share-fill m-0 me-sm-1"></i>
                    <span class="d-none d-sm-inline">Share</span>
                </button>

                <button type="button" class="btn btn-sm btn-secondary select-start">
                    <i class="bi bi-grid-3x3-gap-fill m-0 me-sm-1"></i>
                    <span class="d-none d-sm-inline">Selection Mode</span>
                </button>

                <button type="button" class="btn btn-sm btn-primary me-1 select-action" id="btn-remove-selection-multi" disabled>
                    <i class="bi bi-trash3-fill m-0 me-sm-1"></i>
                    <span class="d-none d-sm-inline">Remove</span>
                </button>

                <button type="button" class="btn btn-sm btn-secondary select-stop">
                    <i class="bi bi-x-diamond-fill m-0 me-sm-1"></i>
                    <span class="d-none d-sm-inline">Cancel</span>
                </button>

            </div>

        </div>

        <div class="grid row w-100 g-0" id="grid" data-cols="sm:3 lg:6"></div>

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

            <button class="btn btn-secondary action no-share" data-action="photo.cover" title="Album cover">
                <i class="bi bi-star-fill"></i>
            </button>

            <button class="btn btn-secondary action no-share" data-action="photo.remove" title="Remove">
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

        <div id="clipboardToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">

            <div class="toast-body text-center">
                URL copied to clipboard.
            </div>

        </div>

    </div>

    <div class="position-fixed bottom-0 start-50 translate-middle-x p-3 mb-5" style="z-index: 110000000;">

        <div id="deleteToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">

            <div class="toast-body text-center">
                URL has been deleted.
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

    <div class="offcanvas offcanvas-bottom offcanvas-bottom-sm" tabindex="-1" id="offcanvas-share-album" aria-labelledby="offcanvasShareLabel">

        <div class="offcanvas-inner bg-white rounded-top mx-auto w-100 px-3">

            <div class="offcanvas-header d-flex align-items-center justify-content-between mb-2 ms-1">

                <h5 class="offcanvas-title" id="offcanvasCreateLabel">
                    Share album
                </h5>

                <div class="text-end">
                    <button type="button" class="btn btn-primary" id="generateTokenBtn" data-album-id="{{ $album->id }}">
                        <i class="bi bi-link-45deg"></i> Generate Link
                    </button>
                </div>

            </div>

            <div class="d-flex offcanvas-body pt-0 small overflow-visible">

                <form action="{{ route('api.tokens.store') }}" id="shareTokenForm" class="w-100">
                @csrf

                    <div id="tokenList" class="token-container d-grid gap-3 overflow-scroll">


                        <div class="no-token rounded p-3 bg-light">
                            These are no sharing links yet.
                        </div>


                        <template id="tokenTpl">
                        <div class="token-wrapper rounded p-3 bg-light">

                            <div class="text-end text-end fst-italic mb-1 expiry">
                                Validity: <span class="expires_at"></span>
                            </div>

                            <div class="d-flex">

                                <input class="form-control">

                                <button type="button" class="btn btn-sm btn-primary ms-2 btn-copy"><i class="bi bi-copy"></i></button>

                                <button type="button" class="btn btn-sm btn-secondary ms-2 btn-calendar d-none"><i class="bi bi-calendar3-event"></i></button>

                                <button type="button" class="btn btn-sm btn-secondary ms-2 btn-delete"><i class="bi bi-trash3"></i></button>

                            </div>

                        </div>
                        </template>

                    </div>

                    <hr class="my-4" />

                    <div class="d-flex justify-content-center mb-3">

                        <button type="button" class="btn btn-secondary w-50 mx-4" data-bs-dismiss="offcanvas">
                            <i class="bi bi-x-lg me-1"></i> Close
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

@endsection

@push('scripts')
@vite('resources/js/album.js')
@endpush
