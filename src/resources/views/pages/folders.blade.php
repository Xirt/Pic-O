@extends('layouts.app')

@section('title', 'Gallery')

@section('content')
    <div class="container-lg px-1 px-md-4 mt-3">

        <div class="d-flex align-items-center justify-content-between mb-2 ms-1">

            <h1 id="title">Folders</h1>

            <div class="toolbar text-end pe-1">

                <button type="button" class="btn btn-sm btn-primary no-select" data-bs-toggle="offcanvas" data-bs-target="#offcanvas-create-album-from-folder" aria-controls="offcanvas-create-album-from-folder">
                    <i class="bi bi-stars m-0 me-sm-1"></i>
                    <span class="d-none d-sm-inline">Create album</span>
                </button>

                <button type="button" class="btn btn-sm btn-primary no-select mx-1" data-bs-toggle="offcanvas" data-bs-target="#offcanvas-add-folder-to-album" aria-controls="offcanvas-add-folder-to-album">
                    <i class="bi bi-plus-lg m-0 me-sm-1"></i>
                    <span class="d-none d-sm-inline">Add to album</span>
                </button>

                <button type="button" class="btn btn-sm btn-primary select-start">
                    <i class="bi bi-grid-3x3-gap-fill m-0 me-sm-1"></i>
                    <span class="d-none d-sm-inline">Selection Mode</span>
                </button>

                <button type="button" class="btn btn-sm btn-primary select-action" data-bs-toggle="offcanvas" data-bs-target="#offcanvas-create-album-from-selection" aria-controls="offcanvas-create-album-from-selection">
                    <i class="bi bi-stars m-0 me-sm-1"></i>
                    <span class="d-none d-sm-inline">Create album</span>
                </button>

                <button type="button" class="btn btn-sm btn-primary select-action mx-1" data-bs-toggle="offcanvas" data-bs-target="#offcanvas-add-selection-to-album" aria-controls="offcanvas-add-selection-to-album">
                    <i class="bi bi-plus-lg m-0 me-sm-1"></i>
                    <span class="d-none d-sm-inline">Add to album</span>
                </button>

                <button type="button" class="btn btn-sm btn-secondary select-stop">
                    <i class="bi bi-x-diamond-fill m-0 me-sm-1"></i>
                    <span class="d-none d-sm-inline">Cancel</span>
                </button>

            </div>

        </div>

        <div class="grid row w-100 g-0" id="grid" data-cols="sm:4 md:6 xl:9">
            <div class="empty-grid text-muted text-center py-4">
                Nothing to show at the moment.
            </div>
        </div>

    </div>

    @include('partials.module-picoview', [
        'showInfoButton'     => true,
        'showDownloadButton' => true
    ])

    @include('partials.photo-info-form')

    @include('partials.offcanvas-create-album-from-selection')

    @include('partials.offcanvas-create-album-from-folder')

    @include('partials.offcanvas-add-selection-to-album')

    @include('partials.offcanvas-add-folder-to-album')

@endsection

@push('scripts')
@vite('resources/js/folders.js')
@endpush
