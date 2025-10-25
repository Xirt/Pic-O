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
    </div>

    @include('partials.module-picoview', [
        'showInfoButton'     => true,
        'showDownloadButton' => true
    ]);

    @include('partials.photo-info-form')

@endsection

@push('scripts')
@vite('resources/js/timeline.js')
@endpush
