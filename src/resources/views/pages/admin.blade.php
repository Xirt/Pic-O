@extends('layouts.app')

@section('title', 'Admin')

@section('content')

<div class="container-lg px-4 mt-3">

    <h1 class="bs-1 mb-4">Administration</h1>

    <div class="d-flex flex-column flex-lg-row w-100 h-100">

        <ul class="nav nav-tabs flex-row flex-lg-column w-100 w-lg-auto mb-3" id="adminTab" role="tablist">

            <li class="nav-item flex-fill text-center" role="presentation">
                <button class="nav-link w-100 text-dark active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button" role="tab" aria-controls="home" aria-selected="true">Home</button>
            </li>

            <li class="nav-item flex-fill text-center" role="presentation">
                <button class="nav-link w-100 text-dark" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab" aria-controls="users" aria-selected="false">Users</button>
            </li>

            <li class="nav-item flex-fill text-center" role="presentation">
                <button class="nav-link w-100 text-dark" id="scanner-tab" data-bs-toggle="tab" data-bs-target="#scanner" type="button" role="tab" aria-controls="scanner" aria-selected="false">Scanner</button>
            </li>

            <li class="nav-item flex-fill text-center" role="presentation">
                <button class="nav-link w-100 text-dark" id="config-tab" data-bs-toggle="tab" data-bs-target="#config" type="button" role="tab" aria-controls="config" aria-selected="false">Config</button>
            </li>

        </ul>

        <div class="tab-content flex-grow-1 pt-3 pt-lg-0 ps-lg-3">

            <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab" tabindex="0">
                Home
            </div>

            <div class="tab-pane fade" id="users" role="tabpanel" aria-labelledby="users-tab" tabindex="0">
                @include('partials.section_users')
            </div>

            <div class="tab-pane fade" id="scanner" role="tabpanel" aria-labelledby="scanner-tab" tabindex="0">
                @include('partials.section_scanner')
            </div>

            <div class="tab-pane fade" id="config" role="tabpanel" aria-labelledby="messages-tab" tabindex="0">
                @include('partials.section_settings')
            </div>

        </div>

    </div>

</div>

@endsection

@push('scripts')
@vite('resources/js/admin.js')
@endpush
