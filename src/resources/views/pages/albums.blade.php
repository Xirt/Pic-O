@extends('layouts.app')

@section('title', 'Albums')

@section('content')

    <div class="container-lg px-2 px-md-4 mt-3">

        <div class="d-flex align-items-center justify-content-between mb-3 ms-1">

            <h1 class="mb-0">
                Albums
            </h1>

            <div class="toolbar text-end pe-1 d-flex align-items-center">

                <button type="button" class="btn btn-sm btn-primary flex-shrink-0 me-2 no-select" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCreateAlbum" aria-controls="offcanvasCreateAlbum">
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

    @include('partials.offcanvas-create-album-from-folder')

    @include('partials.offcanvas-update-album')

    @include('partials.offcanvas-confirm-delete-album')

@endsection

@push('scripts')
@vite('resources/js/albums.js')
@endpush
