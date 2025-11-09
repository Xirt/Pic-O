
<p>Welcome to the administration panel for managing users and their roles. Here you can create, update, and remove user accounts, assign roles, and reset passwords.Use this section to ensure the right people have the right permissions to view your gallery.</p>


<div class="mx-lg-5">

    <div class="text-end my-2 mb-3">
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvas-create-user" aria-controls="offcanvas-create-user">
            <i class="bi bi-person-fill-add me-1"></i> Create user
        </button>
    </div>

    <table class="table table-striped table-hover align-middle small">
        <thead>
            <tr>
                <th scope="col" class="d-none d-sm-table-cell">Name</th>
                <th scope="col">Email</th>
                <th scope="col" class="col-role text-center">Role</th>
                <th scope="col" class="col-actions text-end">&nbsp;</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $user)
                <tr data-user-id="{{ $user->id }}">
                    <td class="d-none d-sm-table-cell">{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td class="text-center">{{ ucfirst($user->role->value) }}</td>
                    <td class="text-end">

                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvas-modify-user" data-user-id="{{ $user->id }}">
                            <i class="bi bi-pencil m-0 me-sm-1"></i>
                            <span class="d-none d-sm-inline">Modify</span>
                        </button>

                        <button type="button" class="btn btn-sm btn-secondary remove-user" data-user-id="{{ $user->id }}">
                            <i class="bi bi-trash m-0 me-sm-1"></i>
                            <span class="d-none d-sm-inline">Delete</span>
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
