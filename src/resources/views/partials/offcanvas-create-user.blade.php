<div class="offcanvas offcanvas-bottom offcanvas-bottom-sm" tabindex="-1" id="offcanvas-create-user" aria-labelledby="offcanvasCreateUserLabel">

    <div class="offcanvas-inner bg-white rounded-top mx-auto w-100 px-3">

        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasCreateUserLabel">Create User</h5>
        </div>

        <div class="offcanvas-body small">

            <form action="{{ route('api.users.store') }}" method="POST" id="createForm" class="w-100">
            @csrf

                <div class="mb-1 row align-items-center">

                    <label for="user-name" class="col-sm-4 col-form-label">Display name</label>

                    <div class="col-sm-8">

                        <div class="input-group input-group-sm">
                            <span class="input-group-text">
                                <i class="bi bi-fonts"></i>
                            </span>
                            <input type="text" class="form-control" id="user-name" name="name" placeholder="User name" required>
                        </div>

                    </div>

                </div>

                <div class="mb-1 row align-items-center">

                    <label for="user-email" class="col-sm-4 col-form-label">Email address</label>

                    <div class="col-sm-8">

                        <div class="input-group input-group-sm">
                            <span class="input-group-text">
                                <i class="bi bi-fonts"></i>
                            </span>
                            <input type="email" class="form-control" id="user-email" name="email" placeholder="Email address" required>
                        </div>

                    </div>

                </div>

                <div class="mb-1 row align-items-center">

                    <label for="user-password" class="col-sm-4 col-form-label">Password</label>

                    <div class="col-sm-8">

                        <div class="input-group input-group-sm">
                            <span class="input-group-text">
                                <i class="bi bi-key"></i>
                            </span>
                            <input type="password" class="form-control" id="user-password" name="password" placeholder="Password" required>
                        </div>

                    </div>

                </div>

                <div class="mb-1 row align-items-center">

                    <label for="user-password-confirm" class="col-sm-4 col-form-label">Password</label>

                    <div class="col-sm-8">

                        <div class="input-group input-group-sm">
                            <span class="input-group-text">
                                <i class="bi bi-key"></i>
                            </span>
                            <input type="password" class="form-control" id="user-password-confirm" name="password_confirmation" placeholder="Retype password" required>
                        </div>

                    </div>

                </div>

                <div class="mb-1 row align-items-center">

                    <label for="user-role" class="col-sm-4 col-form-label">Role</label>

                    <div class="col-sm-4">

                        <div class="input-group input-group-sm">
                            <span class="input-group-text">
                                <i class="bi bi-list"></i>
                            </span>
                            <select class="form-select" id="user-role" name="role" required>
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>

                    </div>

                </div>

                <div class="mb-3 alert form-message" id="createFormMessage"></div>

                <hr />

                <div class="d-flex justify-content-center mt-4 mb-3">

                    <button type="submit" class="btn btn-sm btn-primary w-50 mx-2 mx-sm-4">
                        <i class="bi bi-person-plus me-1"></i> Create
                    </button>

                    <button type="button" class="btn btn-sm btn-secondary w-50 mx-2 mx-sm-4" data-bs-dismiss="offcanvas">
                        <i class="bi bi-x-lg me-1"></i> Cancel
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>