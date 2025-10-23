@extends('layouts.app')

@section('title', 'Login')

@section('content')

    <div class="container-lg px-3 px-md-4 mt-3">

        <div class="row justify-content-center">

            <div class="col-xl-6 col-lg-8 col-md-10">

                <div class="card shadow">

                    <div class="card-header text-center">
                        <h4>Login</h4>
                    </div>

                    <div class="card-body">

                        <form action="{{ route('login.submit') }}" method="POST" id="loginForm">
                        @csrf

                            <div class="mb-3 row align-items-center">

                                <label for="email" class="col-sm-4 col-form-label">Email address</label>

                                <div class="col-sm-8">

                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-fonts"></i>
                                        </span>
                                        <input type="email" class="form-control" id="email" name="email" value="" autocomplete="email" required>
                                    </div>

                                </div>

                            </div>

                            <div class="mb-3 row align-items-center">

                                <label for="password" class="col-sm-4 col-form-label">Password</label>

                                <div class="col-sm-8">

                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="bi bi-key"></i>
                                        </span>
                                        <input type="password" class="form-control" id="password" name="password" value="" autocomplete="current-password" required>
                                    </div>

                                </div>
                            </div>

                            <div class="mb-3 row align-items-center">

                                <label class="col-sm-4 col-form-label"></label>

                                <div class="col-sm-8 d-flex align-items-center">

                                    <div class="form-check form-switch d-flex align-items-center mb-0">
                                        <input class="form-check-input" type="checkbox" name="remember" id="store-session" role="switch" {{ config('settings.session_persistent')  ? 'checked' : '' }}>
                                        <label class="form-check-label ms-3 mb-0 fst-italic" for="store-session">Keep me signed in</label>
                                    </div>

                                </div>

                            </div>

                            <div class="mb-3 alert form-message" id="login-message" aria-live="polite"></div>

                            <hr />

                            <div class="d-flex justify-content-center mt-4 mb-3">

                                <button type="submit" class="btn btn-primary w-50">
                                    <i class="bi bi-box-arrow-in-right me-2"></i> Login
                                </button>

                            </div>

                        </form>

                    </div>

                </div>

            </div>
            
        </div>

    </div>

@endsection

@push('scripts')
@vite('resources/js/login.js')
@endpush
