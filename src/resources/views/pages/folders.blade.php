@extends('layouts.app')

@section('title', 'Gallery')

@section('content')
    <div class="container-lg px-1 px-md-4 mt-3">

        <div class="d-flex align-items-center justify-content-between mb-2 ms-1">

            <h1 id="title">Folders</h1>

            <div class="toolbar text-end pe-1">

                <button type="button" class="btn btn-sm btn-primary select-start">
                    <i class="bi bi-grid-3x3-gap-fill m-0 me-sm-1"></i>
                    <span class="d-none d-sm-inline">Selection Mode</span>
                </button>

                <button type="button" class="btn btn-sm btn-primary select-action" data-bs-toggle="offcanvas" data-bs-target="#offcanvas-create-album" aria-controls="offcanvas-create-album">
                    <i class="bi bi-stars m-0 me-sm-1"></i>
                    <span class="d-none d-sm-inline">Create album</span>
                </button>

                <button type="button" class="btn btn-sm btn-primary select-action mx-1" data-bs-toggle="offcanvas" data-bs-target="#offcanvas-update-album" aria-controls="offcanvas-update-album">
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

    <div class="offcanvas offcanvas-bottom offcanvas-bottom-sm" tabindex="-1" id="offcanvas-create-album" aria-labelledby="offcanvasBottomLabel">

        <div class="offcanvas-inner bg-white rounded-top mx-auto w-100 px-3">

            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="offcanvasBottomLabel">Create album</h5>
            </div>

            <div class="offcanvas-body small overflow-visible">

                <form action="{{ route('api.albums.store') }}" method="POST" id="createAlbumForm" class="w-100">
                @csrf

                    <div class="mb-1 row align-items-center">

                        <label for="name" class="col-4 col-form-label">Album Title</label>

                        <div class="col-8">

                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-fonts"></i>
                                </span>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Album title" value="" required>
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

                    <div class="mb-3 row align-items-center">

                        <label class="col-4 col-form-label">Selection</label>

                        <div class="col-8">

                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-search"></i>
                                </span>
                                <div class="form-control">
                                    <span class="picture-count">0</span> picture(s)
                                </div>
                            </div>

                        </div>

                    </div>

                    <div class="mb-3 alert form-message alert-success">
                        The album has been successfully created.
                    </div>

                    <hr />

                    <div class="d-flex justify-content-center mt-4 mb-3">

                        <div class="d-none container-hidden"></div>

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

    <div class="offcanvas offcanvas-bottom offcanvas-bottom-sm" tabindex="-1" id="offcanvas-update-album" aria-labelledby="offcanvasBottomLabel">

        <div class="offcanvas-inner bg-white rounded-top mx-auto w-100 px-3">

            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="offcanvasBottomLabel">Add to album</h5>
            </div>

            <div class="offcanvas-body small overflow-visible">

                <form action="" method="POST" id="updateAlbumForm" class="w-100">
                @csrf

                    <div class="mb-1 row align-items-center">

                        <label for="albumTitle" class="col-4 col-form-label">Album Title</label>

                        <div class="col-8">

                            <div class="position-relative m-0" data-tpl-option="folderOption" data-tpl-empty="noFolderOption" id="folderSearchSelect">

                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi bi-menu-up"></i>
                                    </span>
                                    <input type="text" class="form-control" id="folder" placeholder="Select a folder" readonly>
                                    <input type="hidden" class="form-control" id="type" name="album_id">
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
                                        <span class="fw-light" data-field='type'></span>
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

                        <label class="col-4 col-form-label">Selection</label>

                        <div class="col-8">

                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-image"></i>
                                </span>
                                <div class="form-control">
                                    <span class="picture-count">0</span> picture(s) selected.
                                </div>
                            </div>

                        </div>

                    </div>

                    <div class="mb-3 alert form-message alert-success">
                        The album has been successfully updated.
                    </div>

                    <hr />

                    <div class="d-flex justify-content-center mt-4 mb-3">

                        <div class="d-none container-hidden"></div>

                        <button type="submit" class="btn btn-primary w-50 mx-4">
                            <i class="bi bi-plus-lg me-1"></i> Add
                        </button>

                        <button type="button" class="btn btn-secondary w-50 mx-4" data-bs-dismiss="offcanvas">
                            <i class="bi bi-x-lg me-1"></i> Cancel
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

@endsection

@push('scripts')
@vite('resources/js/folders.js')
@endpush
