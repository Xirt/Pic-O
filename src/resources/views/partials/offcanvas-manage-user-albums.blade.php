<div class="offcanvas offcanvas-bottom offcanvas-bottom-sm" tabindex="-1" id="offcanvas-manage-user-albums" aria-labelledby="offcanvasManageUserAlbumsLabel">

    <div class="offcanvas-inner bg-white rounded-top mx-auto w-100 px-3">

        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasManageUserAlbumsLabel">
                Manage Albums for <span id="album-user-name"></span>
            </h5>
        </div>

        <div class="offcanvas-body small">

            <input type="hidden" id="album-user-id" value="">

            <!-- Search Albums -->
            <div class="mb-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" class="form-control" id="album-search" placeholder="Type to search...">
                </div>
            </div>

            <!-- Album List -->
            <div id="albums-list-container" class="mb-3">
            </div>

            <div class="my-3 alert form-message" id="albumManagementMessage"></div>

            <hr />

            <!-- Action Buttons -->
            <div class="d-flex justify-content-center mt-4 mb-3">
                <button type="button" class="btn btn-sm btn-primary w-50 mx-2 mx-sm-4" id="save-album-assignments">
                    <i class="bi bi-check-lg me-1"></i> Save
                </button>

                <button type="button" class="btn btn-sm btn-secondary w-50 mx-2 mx-sm-4" data-bs-dismiss="offcanvas">
                    <i class="bi bi-x-lg me-1"></i> Cancel
                </button>
            </div>

        </div>

    </div>

</div>

<!-- Templates -->
<template id="album-loading-tpl">
    <div class="text-center py-4">
        <div class="spinner-border spinner-border-sm" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="text-muted mt-2">Loading albums...</p>
    </div>
</template>

<template id="album-error-tpl">
    <div class="alert alert-danger">
        <span class="error-message">Failed to load albums</span>
    </div>
</template>

<template id="album-empty-tpl">
    <div class="text-center text-muted py-4">
        <i class="bi bi-inbox fs-1"></i>
        <p class="mt-2">No albums found</p>
    </div>
</template>

<template id="album-count-tpl">
    <div class="mb-2 text-muted small d-none">
        Showing <span class="shown-count">0</span> of <span class="total-count">0</span> albums
    </div>
</template>

<template id="album-checkbox-tpl">

    <label class="d-flex align-items-center mb-2 p-2 border rounded bg-white" style="cursor: pointer;">

        <div class="me-2 flex-shrink-0" style="width: 48px; height: 48px;">
            <img class="album-cover rounded" style="width: 100%; height: 100%; object-fit: cover;" />
        </div>

        <div class="flex-grow-1">
            <div class="album-name fw-semibold"></div>
            <small class="text-muted album-photo-count"></small>
        </div>

        <input class="form-check-input album-checkbox ms-auto mt-0 me-2 flex-shrink-0" type="checkbox" value="" style="transform: scale(1.3);">

    </label>

</template>
