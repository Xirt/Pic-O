@extends('layouts.app')

@section('title', 'Albums')

@section('content')

    <div class="container-lg px-2 px-md-4 mt-3">

        <div class="d-flex align-items-center justify-content-between mb-3 ms-1">

            <h1 class="mb-0">
                Albums
            </h1>

            <div class="toolbar text-end pe-1 d-flex align-items-center">

                <button type="button" class="btn btn-sm btn-primary flex-shrink-0 me-2 no-select" data-bs-toggle="offcanvas" data-bs-target="#offcanvas-create-album" aria-controls="offcanvas-create-album">
                    <i class="bi bi-stars m-0 me-sm-1"></i>
                    <span class="d-none d-sm-inline">Create album</span>
                </button>

                <button type="button" class="btn btn-sm btn-primary select-action-single" id="modifyButton">
                    <i class="bi bi-pencil m-0 me-sm-1"></i>
                    <span class="d-none d-sm-inline">Modify</span>
                </button>

                <button type="button" class="btn btn-sm btn-primary select-action mx-2" id="deleteButton">
                    <i class="bi bi-trash3-fill m-0 me-sm-1"></i>
                    <span class="d-none d-sm-inline">Remove</span>
                </button>

                <button type="button" class="btn btn-sm btn-secondary flex-shrink-0 select-stop">
                    <i class="bi bi-x-lg m-0 me-sm-1"></i>
                    <span class="d-none d-sm-inline">Cancel</span>
                </button>

                <button type="button" class="btn btn-sm btn-primary flex-shrink-0 select-start">
                    <i class="bi bi-check2-all m-0 me-sm-1"></i>
                    <span class="d-none d-sm-inline">Selection Mode</span>
                </button>

            </div>

        </div>

        <div class="toolbar text-end pe-1 d-flex justify-content-end align-items-center">

            <div class="input-group input-group-sm me-2 no-select" style="max-width: 400px;">

                <span class="input-group-text" id="search-label-addon1">
                    <i class="bi bi-search"></i>
                </span>

                <input type="text" class="form-control" placeholder="Search term" aria-label="Search" aria-describedby="search-label-addon1" id="inp-search">

                <select class="form-select text-center" id="sel-type" name="type" style="max-width: 150px;">
                        <option value="" selected></option>
                    @foreach (\App\Enums\AlbumType::cases() as $type)
                        <option value="{{ $type->value }}">
                            {{ ucfirst($type->value) }}
                        </option>
                    @endforeach
                </select>

            </div>

            <div class="dropdown input-group-append">

                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="sortDropdownButton" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-gear"></i>
                </button>

                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="sortDropdownButton">

                    <li class="dropdown-header text-muted">Sort by</li>
                    <li>
                        <a class="dropdown-item selected" href="#" data-order="name">
                            <i class="bi bi-check-lg m-0 me-sm-1"></i>
                            <span class="d-none d-sm-inline">Name</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#" data-order="type">
                            <i class="bi bi-check-lg m-0 me-sm-1"></i>
                            <span class="d-none d-sm-inline">Type</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#" data-order="start_date">
                            <i class="bi bi-check-lg m-0 me-sm-1"></i>
                            <span class="d-none d-sm-inline">Date</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#" data-order="photos_count">
                            <i class="bi bi-check-lg m-0 me-sm-1"></i>
                            <span class="d-none d-sm-inline">Photo count</span>
                        </a>
                    </li>

                    <li><hr class="dropdown-divider"></li>

                    <li class="dropdown-header text-muted">Direction</li>
                    <li>
                        <a class="dropdown-item selected" href="#" data-direction="ASC">
                            <i class="bi bi-check-lg m-0 me-sm-1"></i>
                            <span class="d-none d-sm-inline">Ascending</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#" data-direction="DESC">
                            <i class="bi bi-check-lg m-0 me-sm-1"></i>
                            <span class="d-none d-sm-inline">Descending</span>
                        </a>
                    </li>

                </ul>

            </div>

        </div>

        <div class="grid row w-100 g-0" id="grid" data-cols="sm:2 lg:4">
            <div class="empty-grid text-muted text-center py-4">
                Nothing to show at the moment.
            </div>
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

                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-fonts"></i>
                                </span>
                                <input type="text" class="form-control" id="albumTitleCreate" name="name" placeholder="Album title" value="" required>
                            </div>

                        </div>

                    </div>

                    <div class="mb-1 row align-items-center">

                        <label for="type" class="col-4 col-form-label">Type</label>

                        <div class="col-8">

                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-list"></i>
                                </span>
                                <select class="form-select" id="type" name="type">
                                    @foreach (\App\Enums\AlbumType::cases() as $type)
                                        <option value="{{ $type->value }}">
                                            {{ ucfirst($type->value) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                        </div>

                    </div>

                    <div class="mb-1 row align-items-center">

                        <label for="folder" class="col-4 col-form-label">Folder</label>

                        <div class="col-8">

                            <div class="position-relative m-0" data-tpl-option="folderOption" data-tpl-empty="noFolderOption" id="folderSearchSelect">

                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input type="text" class="form-control" id="folder" placeholder="Select a folder" readonly>
                                    <input type="hidden" class="form-control" id="type" name="folder_id">
                                </div>

                                <div class="search-select-wrapper position-absolute bottom-100 start-0 w-100">

                                    <div class="d-flex flex-column bg-white border rounded-2">

                                        <ul class="search-select-list list-group flex-fill overflow-auto border-bottom rounded-0 w-100" id="dropdownMenu"></ul>

                                        <input type="text" class="form-control flex-grow-1 w-auto m-2" placeholder="Search..." id="dropdownInput" aria-expanded="false" autocomplete="off" required />

                                    </div>

                                </div>

                                <template id="folderOption">
                                <li class="list-group-item list-group-item-light list-group-item-action">
                                    <a href="#" class="dropdown-item py-1">
                                    <div class="form-select-option text-truncate">
                                        <b data-field='name'></b><br/>
                                        <span class="fw-light" data-field='path'></span>
                                    </div>
                                    </a>
                                </li>
                                </template>

                                <template id="noFolderOption">
                                <li class="list-group-item list-group-item-light disabled">
                                    <a href="#" class="dropdown-item py-1">
                                    <div class="form-select-option text-truncate">
                                        <i>No folders matching your query</i>
                                    </div>
                                    </a>
                                </li>
                                </template>

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

                        <label for="name" class="col-sm-3 col-form-label">Album Title</label>

                        <div class="col-sm-9">

                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-fonts"></i>
                                </span>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Album title" value="" required>
                            </div>

                        </div>

                    </div>

                    <div class="mb-1 row align-items-center">

                        <label for="type" class="col-sm-3 col-form-label">Type</label>

                        <div class="col-sm-9">

                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-list"></i>
                                </span>
                                <select class="form-select" id="type" name="type">
                                    @foreach (\App\Enums\AlbumType::cases() as $type)
                                        <option value="{{ $type->value }}">
                                            {{ ucfirst($type->value) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                        </div>

                    </div>

                    <div class="mb-1 row align-items-center">

                        <label for="start_date" class="col-sm-3 col-form-label">Start Date</label>

                        <div class="col-sm-9">

                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-calendar"></i>
                                </span>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="">
                            </div>

                        </div>

                    </div>

                    <div class="mb-1 row align-items-center">

                        <label for="end_date" class="col-sm-3 col-form-label">End Date</label>

                        <div class="col-sm-9">

                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-calendar"></i>
                                </span>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="">
                            </div>

                        </div>

                    </div>

                    <div class="mb-1 row align-items-center">

                        <label for="date_precision" class="col-sm-3 col-form-label">Date Precision</label>

                        <div class="col-sm-9">

                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-list"></i>
                                </span>
                                <select class="form-select" id="date_precision" name="date_precision">
                                    @foreach (\App\Enums\DatePrecision::cases() as $precision)
                                        <option value="{{ $precision->value }}">
                                            {{ ucfirst($precision->value) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

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
                You're about to delete <span id="delCount"></span> item(s). This action cannot be undone. Continue?
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
