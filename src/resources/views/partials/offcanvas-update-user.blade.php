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

                    <label for="modifyUserName" class="col-sm-4 col-form-label">Display name</label>

                    <div class="col-sm-8">

                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-fonts"></i>
                            </span>
                            <input type="text" class="form-control" id="modifyUserName" name="name" placeholder="User name" required>
                        </div>

                    </div>

                </div>

                <div class="mb-1 row align-items-center">

                    <label for="modifyUserEmail" class="col-sm-4 col-form-label">Email address</label>

                    <div class="col-sm-8">

                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-envelope"></i>
                            </span>
                            <input type="email" class="form-control" id="modifyUserEmail" name="email" placeholder="Email address" required>
                        </div>

                    </div>

                </div>

                <div class="mb-1 row align-items-center">

                    <label for="modifyUserRole" class="col-sm-4 col-form-label">Role</label>

                    <div class="col-sm-4">

                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-list"></i>
                            </span>
                            <select class="form-select" id="modifyUserRole" name="role" required>
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>

                    </div>

                </div>

                <div class="mb-3 alert form-message" id="modifyFormMessage"></div>

                <hr />

                <div class="d-flex justify-content-center mt-4 mb-3">

                    <input type="hidden" name="user_id" id="modifyUserId" />

                    <button type="submit" class="btn btn-primary w-50 mx-2 mx-sm-4">
                        <i class="bi bi-pencil me-1"></i> Save
                    </button>

                    <button type="button" class="btn btn-secondary w-50 mx-2 mx-sm-4" data-bs-dismiss="offcanvas">
                        <i class="bi bi-x-lg me-1"></i> Cancel
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>