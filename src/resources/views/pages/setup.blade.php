@extends('layouts.app')

@section('title', 'Initial Setup')

@section('content')

<div class="container-lg px-3 px-md-4 mt-3">

    <div class="row justify-content-center">

        <div class="col-xl-6 col-lg-8 col-md-10">

            <div class="card shadow">

                <div class="card-header text-center">
                    <h4>Pic-O Setup</h4>
                </div>

                <div class="card-body">

                    <form action="{{ route('setup.init') }}" method="POST" id="setupForm">
                    @csrf

                        <div class="mb-3 row align-items-center">

                            <label for="name" class="col-sm-4 col-form-label">Name</label>

                            <div class="col-sm-8">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required autofocus>
                                </div>
                            </div>

                        </div>

                        <div class="mb-3 row align-items-center">

                            <label for="email" class="col-sm-4 col-form-label">Email address</label>

                            <div class="col-sm-8">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
                                </div>
                            </div>

                        </div>

                        <div class="mb-3 row align-items-center">

                            <label for="password" class="col-sm-4 col-form-label">Password</label>

                            <div class="col-sm-8">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-key"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" autocomplete="new-password" required>
                                </div>
                            </div>

                        </div>

                        <div class="mb-3 row align-items-center">

                            <label for="password_confirmation" class="col-sm-4 col-form-label">Confirm password</label>

                            <div class="col-sm-8">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" autocomplete="new-password" required>
                                </div>
                            </div>

                        </div>

                        @if ($errors->any())
                            <div class="mb-3 alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <hr />

                        <div class="d-flex justify-content-center mt-4 mb-3">

                            <button type="submit" class="btn btn-primary w-50">
                                <i class="bi bi-check-circle me-2"></i> Complete Setup
                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection
