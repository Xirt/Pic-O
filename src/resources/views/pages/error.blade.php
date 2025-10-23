@extends('layouts.app')

@section('title', 'Page not found')

@section('content')

    <div class="container px-3 px-md-4 mt-3">

        <div class="row justify-content-center">

            <div class="col-xl-6 col-lg-8 col-md-10">

                <div class="card shadow">

                    <div class="card-header text-center">
                        <h4>Where did that page go?</h4>
                    </div>

                    <div class="card-body text-muted">

                        <p>We looked everywhere, but we couldn't find the page you were trying to reach.</p>
                        <p>Maybe it was moved, deleted, or never existed in the first place.</p>

                        <hr />

                        <div class="d-flex justify-content-center mt-4 mb-3">

                            <a href="{{ route('home') }}" class="btn btn-primary w-50">
                                <i class="bi bi-house-fill me-2"></i>
                                Return to Homepage
                            </a>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

@endsection

@push('scripts')
@endpush
