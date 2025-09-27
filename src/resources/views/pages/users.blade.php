@extends('layouts.app')

@section('title', 'Users')

@section('content')
    <div class="container px-4">

        <div class="d-flex align-items-center justify-content-between mb-3 ms-1">
            <h1 class="mb-0">Users</h1>
            <div class="ms-3">
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvas-create-user" aria-controls="offcanvas-create-user">
                    <i class="bi bi-stars"></i> Add user
                </button>
            </div>
        </div>

       <table class="table table-striped table-hover align-middle">
            <thead>
                <tr>
                    <th scope="col" class="col-id text-center">#</th>
                    <th scope="col">Name</th>
                    <th scope="col">Email</th>
                    <th scope="col" class="col-role text-center">Role</th>
                    <th scope="col" class="col-actions text-end">&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <th scope="row" class="text-center">{{ $user->id }}</th>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td class="text-center">{{ ucfirst($user->role->value) }}</td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvas-modify-user" data-user-id="{{ $user->id }}">
                                <i class="bi bi-pencil me-1"></i> Modify
                            </button>

                            <button type="button" class="btn btn-sm btn-secondary" onclick="confirmUserDelete({{ $user->id }})">
                                <i class="bi bi-trash me-1"></i> Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>

    <div class="offcanvas offcanvas-bottom offcanvas-bottom-sm" tabindex="-1" id="offcanvas-create-user" aria-labelledby="offcanvasCreateUserLabel">

        <div class="offcanvas-inner bg-white rounded-top mx-auto w-100 px-3">

            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="offcanvasCreateUserLabel">Create User</h5>
            </div>

            <div class="offcanvas-body small">

                <form action="{{ route('api.users.store') }}" method="POST" id="createForm" class="w-100">
                @csrf

                    <div class="mb-1 row align-items-center">
                        <label for="userName" class="col-4 col-form-label">Display name</label>
                        <div class="col-8">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-fonts"></i>
                                </span>
                                <input type="text" class="form-control" id="userName" name="name" placeholder="User name" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-1 row align-items-center">
                        <label for="userEmail" class="col-4 col-form-label">Email address</label>
                        <div class="col-8">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-fonts"></i>
                                </span>
                                <input type="email" class="form-control" id="userEmail" name="email" placeholder="Email address" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-1 row align-items-center">
                        <label for="userPassword" class="col-4 col-form-label">Password</label>
                        <div class="col-8">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-key"></i>
                                </span>
                                <input type="password" class="form-control" id="userPassword" name="password" placeholder="Password" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-1 row align-items-center">
                        <label for="userPasswordConfirm" class="col-4 col-form-label">Password (check)</label>
                        <div class="col-8">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-key"></i>
                                </span>
                                <input type="password" class="form-control" id="userPasswordConfirm" name="password_confirmation" placeholder="Retype password" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-1 row align-items-center">
                        <label for="userRole" class="col-4 col-form-label">Role</label>
                        <div class="col-4">
                            <select class="form-select" id="userRole" name="role" required>
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3 alert form-message" id="createFormMessage"></div>

                    <hr />

                    <div class="d-flex justify-content-center mt-4 mb-3">
                        <button type="submit" class="btn btn-primary w-50 mx-4">
                            <i class="bi bi-person-plus me-1"></i> Create
                        </button>

                        <button type="button" class="btn btn-secondary w-50 mx-4" data-bs-dismiss="offcanvas">
                            <i class="bi bi-x-lg me-1"></i> Cancel
                        </button>
                    </div>

                </form>

            </div>

        </div>

    </div>

    <div class="offcanvas offcanvas-bottom offcanvas-bottom-sm" tabindex="-1" id="offcanvas-modify-user" aria-labelledby="offcanvasModifyUserLabel">

        <div class="offcanvas-inner bg-white rounded-top mx-auto w-100 px-3">

            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="offcanvasModifyUserLabel">Modify User</h5>
            </div>

            <div class="offcanvas-body small">

                <form action="#" method="POST" id="modifyForm" class="w-100">
                    @csrf
                    @method('PUT')

                    <div class="mb-1 row align-items-center">
                        <label for="modifyUserName" class="col-4 col-form-label">Display name</label>
                        <div class="col-8">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-fonts"></i>
                                </span>
                                <input type="text" class="form-control" id="modifyUserName" name="name" placeholder="User name" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-1 row align-items-center">
                        <label for="modifyUserEmail" class="col-4 col-form-label">Email address</label>
                        <div class="col-8">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input type="email" class="form-control" id="modifyUserEmail" name="email" placeholder="Email address" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-1 row align-items-center">
                        <label for="modifyUserRole" class="col-4 col-form-label">Role</label>
                        <div class="col-4">
                            <select class="form-select" id="modifyUserRole" name="role" required>
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3 alert form-message" id="modifyFormMessage"></div>

                    <hr />

                    <div class="d-flex justify-content-center mt-4 mb-3">

                        <input type="hidden" name="user_id" id="modifyUserId" />

                        <button type="submit" class="btn btn-primary w-50 mx-4">
                            <i class="bi bi-pencil me-1"></i> Save
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
@vite('resources/js/users.js')
@endpush
