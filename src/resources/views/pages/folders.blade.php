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

            <template id="card-folder">
            <div class="grid-item card folder selectable clickable my-1">

                <div class="card-body text-primary position-relative w-100 ratio ratio-4x3 p-3">

                    <div class="d-flex align-items-center justify-content-center folder-icon-wrapper">

                        <img class="folder-icon h-75 w-75" src="images/folder.png">
                        <img class="folder-thumb position-absolute w-75 mt-2">

                    </div>

                </div>

                <div class="card-footer text-center text-truncate overflow-hidden folder-name"></div>

            </div>
            </template>    

            <template id="card-file">
            <a class="grid-item card file selectable clickable thumbnail my-1"
               data-type="image">

                <div class="card-body text-primary position-relative w-100 ratio ratio-4x3 p-3 file-body">

                    <div class="d-flex align-items-center justify-content-center">
                        <i class="bi bi-check-circle-fill opacity-75 file-selected-icon"></i>
                    </div>

                </div>

                <div class="card-footer text-center text-truncate overflow-hidden file-name"></div>

            </a>
            </template>

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
