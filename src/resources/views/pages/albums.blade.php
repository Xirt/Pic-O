@extends('layouts.app')

@section('title', 'Albums')

@section('content')

    <div class="container-lg px-4">

        <div class="d-flex align-items-center justify-content-between mb-3 ms-1">

            <h1 class="mb-0">
                Albums
            </h1>

            <div class="toolbar text-end pe-1 d-flex align-items-center">

                <div class="input-group input-group-sm  no-select">

                    <span class="input-group-text" id="search-label-addon1">
                        <i class="bi bi-search"></i>
                    </span>

                    <input type="text" class="form-control" placeholder="Search term" aria-label="Search" aria-describedby="search-label-addon1" id="inp-search">

                </div>

                <div class="dropdown mx-2 no-select">

                    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="sortDropdownButton" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-gear"></i>
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="sortDropdownButton">

                        <li class="dropdown-header text-muted">Sort by</li>
                        <li>
                            <a class="dropdown-item selected" href="#" data-order="name">
                                <i class="bi bi-check-lg me-1"></i> Name
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#" data-order="created_at">
                                <i class="bi bi-check-lg me-1"></i> Creation Date
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#" data-order="photos_count">
                                <i class="bi bi-check-lg me-1"></i> Photo count
                            </a>
                        </li>

                        <li><hr class="dropdown-divider"></li>

                        <li class="dropdown-header text-muted">Direction</li>
                        <li>
                            <a class="dropdown-item selected" href="#" data-direction="ASC">
                                <i class="bi bi-check-lg me-1"></i> Ascending
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#" data-direction="DESC">
                                <i class="bi bi-check-lg me-1"></i> Descending
                            </a>
                        </li>

                    </ul>

                </div>

                <button type="button" class="btn btn-sm btn-primary flex-shrink-0 me-2 no-select" data-bs-toggle="offcanvas" data-bs-target="#offcanvas-create-album" aria-controls="offcanvas-create-album">
                    <i class="bi bi-stars"></i> Create album
                </button>

                <button type="button" class="btn btn-sm btn-primary select-action">
                    <i class="bi bi-pencil"></i> Modify
                </button>

                <button type="button" class="btn btn-sm btn-primary select-action mx-2">
                    <i class="bi bi-trash3-fill"></i> Remove
                </button>

                <button type="button" class="btn btn-sm btn-secondary flex-shrink-0 select-stop">
                    <i class="bi bi-x-lg"></i> Cancel
                </button>

                <button type="button" class="btn btn-sm btn-primary flex-shrink-0 select-start">
                    <i class="bi bi-check2-all"></i> Selection Mode
                </button>

            </div>

        </div>

        <div id="grid" class="grid grid-large row w-100 g-0">
           <div class="grid-sizer"></div>
           <div class="gutter-sizer"></div>
           <div class="message-empty p-1">There are currently no items to display.</div>
        </div>

    </div>

    <div class="offcanvas offcanvas-bottom offcanvas-bottom-sm" tabindex="-1" id="offcanvas-create-album" aria-labelledby="offcanvasCreateLabel">

        <div class="offcanvas-inner bg-white rounded-top mx-auto w-100 px-3">

            <div class="offcanvas-header">

                <h5 class="offcanvas-title" id="offcanvasCreateLabel">
                    Create album
                </h5>

            </div>

            <div class="offcanvas-body small overflow-visible">

                <form action="{{ route('api.albums.storeFromFolder') }}" method="POST" id="createAlbumForm" class="w-100">
                @csrf

                    <div class="mb-1 row align-items-center">

                        <label for="albumTitleCreate" class="col-4 col-form-label">Album Title</label>

                        <div class="col-8">
                            <input type="text" class="form-control" id="albumTitleCreate" name="name" placeholder="Album title" value="" required>
                        </div>

                    </div>

                    <div class="mb-1 row align-items-center">

                        <label class="col-4 col-form-label">Folder</label>

                        <div class="col-8">

                            <div class="dropup position-relative" id="search-dropdown-container">

                                <input type="text" class="form-control" placeholder="Search..." id="dropdownInput" data-bs-toggle="dropdown" aria-expanded="false" autocomplete="off" required />
                                <ul class="dropdown-menu w-100" id="dropdownMenu"></ul>

                                <input type="hidden" name="folder_id" id="dropdownHidden">

                            </div>

                        </div>

                    </div>

                    <div class="mb-3 row align-items-center">

                        <label class="col-4 col-form-label">Scope</label>

                        <div class="col-8 d-flex align-items-center">
                            <div class="form-check form-switch d-flex align-items-center mb-0">
                                <input class="form-check-input" type="checkbox" name="subdirectories" id="subDirSwitch" data-label-on="Include subdirectories" data-label-off="Exclude subdirectories">
                                <label class="form-check-label ms-2 mb-0 fst-italic" for="subDirSwitch">Exclude subdirectories</label>
                            </div>
                        </div>

                    </div>

                    <hr />

                    <div class="d-flex justify-content-center mt-4 mb-3">

                        <button type="submit" class="btn btn-primary w-50 mx-4">
                            <i class="bi bi-stars me-1"></i> Create
                        </button>

                        <button type="button" class="btn btn-secondary w-50 mx-4" data-bs-dismiss="offcanvas">
                            <i class="bi bi-x-lg me-1"></i> Cancel
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

    <div class="offcanvas offcanvas-bottom offcanvas-bottom-sm" tabindex="-1" id="offcanvas-modify-album" aria-labelledby="offcanvasModifyLabel">

        <div class="offcanvas-inner bg-white rounded-top mx-auto w-100 px-3">

            <div class="offcanvas-header">

                <h5 class="offcanvas-title" id="offcanvasModifyLabel">
                    Modify album
                </h5>

            </div>

            <div class="offcanvas-body small overflow-visible">

                <form action="" method="POST" id="updateAlbumForm" class="w-100">
                @csrf

                    <div class="mb-1 row align-items-center">

                        <label for="albumTitleUpdate" class="col-4 col-form-label">Album Title</label>

                        <div class="col-8">
                            <input type="text" class="form-control" id="albumTitleUpdate" name="name" placeholder="Album title" value="" required>
                        </div>

                    </div>

                    <hr />

                    <div class="d-flex justify-content-center mt-4 mb-3">

                        <input type="hidden" class="form-control" id="albumId" name="id" placeholder="Album title" value="">

                        <button type="submit" class="btn btn-primary w-50 mx-4">
                            <i class="bi bi-save me-1"></i> Save
                        </button>

                        <button type="button" class="btn btn-secondary w-50 mx-4" data-bs-dismiss="offcanvas">
                            <i class="bi bi-x-lg me-1"></i> Cancel
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

    <div class="offcanvas offcanvas-bottom offcanvas-bottom-sm" tabindex="-1" id="offcanvas-remove-album" aria-labelledby="offcanvasRemoveLabel">

        <div class="offcanvas-inner bg-white rounded-top mx-auto w-100 px-3">

            <div class="offcanvas-header">

                <h5 class="offcanvas-title" id="offcanvasRemoveLabel">
                    Confirm deletion
                </h5>

            </div>

            <div class="offcanvas-body py-2 small">
                Are you sure you want to delete this album? This action cannot be undone.
            </div>

            <hr />

            <div class="d-flex justify-content-center mt-4 mb-3">

                <button type="button" class="btn btn-primary w-50 mx-4" id="btn-remove">
                    <i class="bi bi-trash3 me-1"></i> Delete
                </button>

                <button type="button" class="btn btn-secondary w-50 mx-4" data-bs-dismiss="offcanvas">
                    <i class="bi bi-x-lg me-1"></i> Cancel
                </button>

            </div>

        </div>

    </div>

@endsection

@push('scripts')
@vite('resources/js/albums.js')
@endpush
