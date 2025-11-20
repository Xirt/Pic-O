<div class="offcanvas offcanvas-bottom offcanvas-bottom-sm" tabindex="-1" id="offcanvas-update-user" aria-labelledby="offcanvasUpdateUserLabel">

    <div class="offcanvas-inner bg-white rounded-top mx-auto w-100 px-3">

        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasUpdateUserLabel">Modify User</h5>
        </div>

        <div class="offcanvas-body small">

            <form action="#" method="POST" id="updateUserForm" class="w-100">
                @csrf
                @method('PUT')

                <div class="mb-1 row align-items-center">

                    <label for="updateUserName" class="col-sm-4 col-form-label">Display name</label>

                    <div class="col-sm-8">

                        <div class="input-group input-group-sm">
                            <span class="input-group-text">
                                <i class="bi bi-fonts"></i>
                            </span>
                            <input type="text" class="form-control" id="updateUserName" name="name" placeholder="User name" required>
                        </div>

                    </div>

                </div>

                <div class="mb-1 row align-items-center">

                    <label for="updateUserEmail" class="col-sm-4 col-form-label">Email address</label>

                    <div class="col-sm-8">

                        <div class="input-group input-group-sm">
                            <span class="input-group-text">
                                <i class="bi bi-envelope"></i>
                            </span>
                            <input type="email" class="form-control" id="updateUserEmail" name="email" placeholder="Email address" required>
                        </div>

                    </div>

                </div>

                <div class="mb-1 row align-items-center">

                    <label for="updatePassword" class="col-sm-4 col-form-label">Password</label>

                    <div class="col-sm-8">

                        <div class="input-group input-group-sm">
                            <span class="input-group-text">
                                <i class="bi bi-key"></i>
                            </span>
                            <input type="password" class="form-control" id="updatePassword" name="password" placeholder="Password (optional)">
                        </div>

                    </div>

                </div>

                <div class="mb-1 row align-items-center">

                    <label for="updatePasswordConf" class="col-sm-4 col-form-label">Password (verify)</label>

                    <div class="col-sm-8">

                        <div class="input-group input-group-sm">
                            <span class="input-group-text">
                                <i class="bi bi-key"></i>
                            </span>
                            <input type="password" class="form-control" id="updatePasswordConf" name="password_confirmation" placeholder="Password (verification)">
                        </div>

                    </div>

                </div>

                <div class="mb-1 row align-items-center">

                    <label for="updateUserRole" class="col-sm-4 col-form-label">Role</label>

                    <div class="col-sm-4">


                    @if(auth()->user()->role->value === 'admin')

                        <div class="input-group input-group-sm">
                            <span class="input-group-text">
                                <i class="bi bi-list"></i>
                            </span>
                            <select class="form-select" id="updateUserRole" name="role" required>
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>

                    @else

                        <div class="input-group input-group-sm">
                            <span class="input-group-text">
                                <i class="bi bi-list"></i>
                            </span>
                            <input type="text" class="form-control" name="role" readonly>
                        </div>

                    @endif

                    </div>

                </div>

                <div class="my-3 alert form-message"></div>

                <hr />

                <div class="d-flex justify-content-center mt-4 mb-3">

                    <input type="hidden" name="id" />

                    <button type="submit" class="btn btn-sm btn-primary w-50 mx-2 mx-sm-4">
                        <i class="bi bi-pencil me-1"></i> Save
                    </button>

                    <button type="button" class="btn btn-sm btn-secondary w-50 mx-2 mx-sm-4" data-bs-dismiss="offcanvas">
                        <i class="bi bi-x-lg me-1"></i> Cancel
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>