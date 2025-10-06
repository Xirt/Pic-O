@extends('layouts.app')

@section('title', 'Admin')

@section('content')

<div class="container-lg px-4">

    <div class="mb-3 ms-1">
        <h1 class="mb-0">Administration</h1>
    </div>

    <div class="d-flex align-items-start w-100 h-100 ">

        <div class="nav flex-column nav-pills me-2" id="v-pills-tab" role="tablist" aria-orientation="vertical">
            <button class="nav-link mb-2 px-5 active" id="v-pills-home-tab" data-bs-toggle="pill" data-bs-target="#v-pills-home" type="button" role="tab" aria-controls="v-pills-home" aria-selected="true">Home</button>
            <button class="nav-link mb-2 px-5" id="v-pills-users-tab" data-bs-toggle="pill" data-bs-target="#v-pills-users" type="button" role="tab" aria-controls="v-pills-users" aria-selected="false">Users</button>
            <button class="nav-link mb-2 px-5" id="v-pills-scanner-tab" data-bs-toggle="pill" data-bs-target="#v-pills-scanner" type="button" role="tab" aria-controls="v-pills-scanner" aria-selected="false">Scanner</button>
            <button class="nav-link px-5" id="v-pills-messages-tab" data-bs-toggle="pill" data-bs-target="#v-pills-messages" type="button" role="tab" aria-controls="v-pills-messages" aria-selected="false">Config</button>
        </div>

        <div class="tab-content w-100 p-2 border-start" id="v-pills-tabContent">

            <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab" tabindex="0">
                Home
            </div>

            <div class="tab-pane fade" id="v-pills-users" role="tabpanel" aria-labelledby="v-pills-users-tab" tabindex="0">
                @include('partials.section_users')
            </div>

            <div class="tab-pane fade" id="v-pills-scanner" role="tabpanel" aria-labelledby="v-pills-scanner-tab" tabindex="0">
                @include('partials.section_jobs')
            </div>

            <div class="tab-pane fade" id="v-pills-messages" role="tabpanel" aria-labelledby="v-pills-messages-tab" tabindex="0">
                @include('partials.section_settings')
            </div>

        </div>

    </div>

</div>

@endsection

@push('scripts')
@vite('resources/js/admin.js')
@endpush
