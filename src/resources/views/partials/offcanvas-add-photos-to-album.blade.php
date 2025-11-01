<div class="offcanvas offcanvas-bottom offcanvas-bottom-sm" tabindex="-1" id="offcanvas-update-album" aria-labelledby="offcanvasBottomLabel">

    <div class="offcanvas-inner bg-white rounded-top mx-auto w-100 px-3">

        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasBottomLabel">Add to album</h5>
        </div>

        <div class="offcanvas-body small overflow-visible">

            <form action="" method="POST" id="updateAlbumForm" class="w-100">
            @csrf

                <div class="mb-1 row align-items-center">

                    <label for="album" class="col-sm-4 col-form-label">Album Title</label>

                    <div class="col-sm-8">

                        <div class="position-relative m-0" data-tpl-option="folderOption" data-tpl-empty="noFolderOption" id="folderSearchSelect">

                            <div class="input-group input-group-sm">
                                <span class="input-group-text">
                                    <i class="bi bi bi-menu-up"></i>
                                </span>
                                <input type="text" class="form-control" id="album" placeholder="Select an album" readonly>
                                <input type="hidden" class="form-control" id="type" name="album_id">
                            </div>

                            <div class="search-select-wrapper position-absolute bottom-100 start-0 w-100">

                                <div class="d-flex flex-column bg-white border rounded-2">

                                    <ul class="search-select-list list-group flex-fill overflow-auto border-bottom rounded-0 w-100" id="dropdownMenu"></ul>

                                    <input type="text" class="form-control flex-grow-1 w-auto m-2" placeholder="Search..." id="dropdownInput" aria-expanded="false" autocomplete="off" required />

                                </div>

                            </div>

                            <template id="folderOption">
                            <li class="list-group-item list-group-item-light list-group-item-action">
                                <a href="#" class="dropdown-item py-1">
                                <div class="form-select-option text-truncate">
                                    <b data-field='name'></b><br/>
                                    <span class="fw-light" data-field='type'></span>
                                </div>
                                </a>
                            </li>
                            </template>

                            <template id="noFolderOption">
                            <li class="list-group-item list-group-item-light disabled">
                                <a href="#" class="dropdown-item py-1">
                                <div class="form-select-option text-truncate">
                                    <i>No folders matching your query</i>
                                </div>
                                </a>
                            </li>
                            </template>

                        </div>

                    </div>

                </div>

                <div class="mb-1 row align-items-center">

                    <label class="col-sm-4 col-form-label">Selection</label>

                    <div class="col-sm-8">

                        <div class="input-group input-group-sm">
                            <span class="input-group-text">
                                <i class="bi bi-image"></i>
                            </span>
                            <div class="form-control">
                                <span class="picture-count">0</span> picture(s) selected.
                            </div>
                        </div>

                    </div>

                </div>

                <div class="my-3 alert form-message alert-success show">
                    The album has been successfully updated.
                </div>

                <hr />

                <div class="d-flex justify-content-center mt-4 mb-3">

                    <div class="d-none container-hidden"></div>

                    <button type="submit" class="btn btn-sm btn-primary w-50 mx-2 mx-sm-4">
                        <i class="bi bi-plus-lg me-1"></i> Add
                    </button>

                    <button type="button" class="btn btn-sm btn-secondary w-50 mx-2 mx-sm-4" data-bs-dismiss="offcanvas">
                        <i class="bi bi-x-lg me-1"></i> Cancel
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>