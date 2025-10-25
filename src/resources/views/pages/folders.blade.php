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

        <div class="grid row w-100 g-0" id="grid" data-cols="sm:4 md:6 xl:9"></div>    

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

    <div class="offcanvas offcanvas-bottom offcanvas-bottom-sm" tabindex="-1" id="offcanvas-create-album" aria-labelledby="offcanvasBottomLabel">

        <div class="offcanvas-inner bg-white rounded-top mx-auto w-100 px-3">

            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="offcanvasBottomLabel">Create album</h5>
            </div>

            <div class="offcanvas-body small">

                <form action="{{ route('api.albums.store') }}" method="POST" id="createAlbumForm" class="w-100">
                @csrf

                    <div class="mb-1 row align-items-center">

                        <label for="albumTitle" class="col-4 col-form-label">Album Title</label>

                        <div class="col-8">

                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-fonts"></i>
                                </span>
                                <input type="text" class="form-control" id="albumTitle" name="name" placeholder="Album title" value="" required>
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

            <div class="offcanvas-body small">

                <form action="" method="POST" id="updateAlbumForm" class="w-100">
                @csrf

                    <div class="mb-1 row align-items-center">

                        <label for="albumTitle" class="col-4 col-form-label">Album Title</label>

                        <div class="col-8">

                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-fonts"></i>
                                </span>
                                <div class="dropup position-relative form-control p-0" id="search-dropdown-container">

                                    <input type="text" class="form-control border-0" placeholder="Search..." id="dropdownInput" data-bs-toggle="dropdown" aria-expanded="false" autocomplete="off" required />
                                    <ul class="dropdown-menu w-100" id="dropdownMenu"></ul>

                                    <input type="hidden" name="album_id" id="dropdownHidden">

                                </div>

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
