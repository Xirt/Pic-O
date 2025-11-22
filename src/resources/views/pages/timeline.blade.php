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

        <template id="grid-separator">
            <h5 class="text-dark-emphasis p-2 pb-1 mt-4 fw-semibold border-bottom separator-title"></h5>
        </template>

        <template id="card-photo">
        <a class="grid-item clickable selectable position-relative thumbnail w-100 p-0 my-1" data-type="image">

            <i class="bi bi-check-circle-fill position-absolute top-50 start-50 translate-middle photo-selected-icon"></i>

            <canvas class="photo-blurhash d-block w-100 h-100 m-0 blurhash"></canvas>

            <img class="photo-thumb d-block w-100 h-100 opacity-0 m-0" loading="lazy">

            <div class="card-img-overlay bg-dark text-light fw-semibold quick-actions top p-1 photo-toolbar">

                <button class="btn btn-light btn-sm me-1 btn-info">
                    <i class="bi bi-info-circle-fill text-secondary"></i>
                </button>

                <button class="btn btn-light btn-sm me-1 btn-download">
                    <i class="bi bi-download text-secondary"></i>
                </button>

            </div>

        </a>
        </template>

    </div>


    @include('partials.module-picoview', [
        'showInfoButton'     => true,
        'showDownloadButton' => true
    ])

    @include('partials.photo-info-form')

@endsection

@push('scripts')
@vite('resources/js/timeline.js')
@endpush
