@extends('layouts.app')

@section('title', 'Albums')

@section('content')

    <div class="container-lg px-2 px-md-4 mt-3">

        <div class="d-flex align-items-center justify-content-between mb-3 ms-1">

            <h1 class="mb-0">
                Albums
            </h1>

            <div class="toolbar text-end pe-1 d-flex align-items-center">

                @can('create', \App\Models\Album::class)
                <button type="button" class="btn btn-sm btn-primary flex-shrink-0 me-2 no-select" data-bs-toggle="offcanvas" data-bs-target="#offcanvas-create-album-from-folder" aria-controls="offcanvas-create-album-from-folder">
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
                @endcan

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

                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="sortDropdownButton" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-gear"></i>
                </button>

                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="sortDropdownButton">

                    <li class="dropdown-header text-muted">Sort by</li>
                    <li>
                        <a class="dropdown-item {{ config('settings.album_sorting_type') == 'name'  ? 'selected' : '' }}" href="#" data-order="name">
                            <i class="bi bi-check-lg m-0 me-sm-1"></i>
                            <span class="d-none d-sm-inline">Name</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item {{ config('settings.album_sorting_type') == 'type'  ? 'selected' : '' }}" href="#" data-order="type">
                            <i class="bi bi-check-lg m-0 me-sm-1"></i>
                            <span class="d-none d-sm-inline">Type</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item {{ config('settings.album_sorting_type') == 'start_date'  ? 'selected' : '' }}" href="#" data-order="start_date">
                            <i class="bi bi-check-lg m-0 me-sm-1"></i>
                            <span class="d-none d-sm-inline">Date</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item {{ config('settings.album_sorting_type') == 'photos_count'  ? 'selected' : '' }}" href="#" data-order="photos_count">
                            <i class="bi bi-check-lg m-0 me-sm-1"></i>
                            <span class="d-none d-sm-inline">Photo count</span>
                        </a>
                    </li>

                    <li><hr class="dropdown-divider"></li>

                    <li class="dropdown-header text-muted">Direction</li>
                    <li>
                        <a class="dropdown-item {{ config('settings.album_sorting_direction') == 'asc'  ? 'selected' : '' }}" href="#" data-direction="ASC">
                            <i class="bi bi-check-lg m-0 me-sm-1"></i>
                            <span class="d-none d-sm-inline">Ascending</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item {{ config('settings.album_sorting_direction') == 'desc'  ? 'selected' : '' }}" href="#" data-direction="DESC">
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

            <template id="card-album">
            <a class="grid-item card clickable selectable position-relative rounded-0 ratio ratio-4x3 w-100 p-0 my-1 border-0">

                <canvas class="album-blurhash d-block position-absolute object-fit-cover w-100 h-100"></canvas>

                <img class="album-thumb d-block object-fit-cover" loading="lazy">

                <i class="bi bi-check-circle-fill opacity-75 h-auto w-auto position-absolute top-50 start-50 translate-middle album-selected-icon"></i>

                <div class="card-img-overlay bottom badge rounded-pill bg-secondary text-light fw-bold px-2 py-1 m-1 me-2 album-photo-count"></div>

                <div class="card-img-overlay top rounded-0 bg-dark text-light fw-semibold text-truncate px-2 py-1 p-lg-2 album-title"></div>

                <div class="d-block quick-actions p-1 pe-2 album-toolbar">

                    @can('create', \App\Models\Album::class)
                    <button class="btn btn-light btn-sm me-1 btn-modify no-share">
                        <i class="bi bi-pencil-fill text-secondary"></i>
                    </button>

                    <button class="btn btn-light btn-sm btn-delete no-share">
                        <i class="bi bi-trash3 text-secondary"></i>
                    </button>
                    @endcan

                </div>

            </a>
            </template>

        </div>

    </div>

    @can('create', \App\Models\Album::class)

    @include('partials.offcanvas-create-album-from-folder')
    @include('partials.offcanvas-update-album')
    @include('partials.offcanvas-confirm-delete-album')

    @endcan

@endsection

@push('scripts')
@vite('resources/js/albums.js')
@endpush
