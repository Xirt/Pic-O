<div class="offcanvas offcanvas-bottom offcanvas-bottom-sm" tabindex="-1" id="offcanvas-add-folder-to-album" aria-labelledby="offcanvasAddFolderToAlbumLabel">

    <div class="offcanvas-inner bg-white rounded-top mx-auto w-100 px-3">

        <div class="offcanvas-header">

            <h5 class="offcanvas-title" id="offcanvasAddFolderToAlbumLabel">
                Add folder to album
            </h5>

        </div>

        <div class="offcanvas-body small overflow-visible">

            <form action="" method="POST" id="addFolderToAlbumForm" class="w-100">
            @csrf

                <input type="hidden" id="folderId" name="folder_id">

                <div class="mb-1 row align-items-center">

                    <label for="add_folder_album" class="col-sm-4 col-form-label">Album Title</label>

                    <div class="col-sm-8">

                        <div class="position-relative m-0" data-tpl-option="albumOption" data-tpl-empty="noAlbumOption" id="addFolderAlbum">

                            <div class="input-group input-group-sm">
                                <span class="input-group-text">
                                    <i class="bi bi bi-menu-up"></i>
                                </span>
                                <input type="text" class="form-control" id="add_folder_album" placeholder="Select an album" readonly>
                                <input type="hidden" id="type" name="album_id">
                            </div>

                            <div class="search-select-wrapper position-absolute bottom-100 start-0 w-100">

                                <div class="d-flex flex-column bg-white border rounded-2">

                                    <ul class="search-select-list list-group flex-fill overflow-auto border-bottom rounded-0 w-100" id="dropdownMenu"></ul>

                                    <input type="text" class="form-control flex-grow-1 w-auto m-2" placeholder="Search..." id="dropdownInput" aria-expanded="false" autocomplete="off" required />

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                <div class="mb-1 row align-items-center">

                    <label class="col-sm-4 col-form-label">Folder contents</label>

                    <div class="col-sm-8">

                        <div class="input-group input-group-sm">
                            <span class="input-group-text">
                                <i class="bi bi-image"></i>
                            </span>
                            <div class="form-control">
                                <span id="photoCount">0</span> picture(s), excl. subdirectories
                            </div>
                        </div>

                    </div>

                </div>

                <div class="mb-1 mt-2 mt-sm-0 row align-items-center">

                    <label for="add_folder_subdirectories" class="col-sm-4 col-form-label d-none d-sm-block">Scope</label>

                    <div class="col-sm-8 d-flex align-items-center">
                        <div class="form-check form-switch d-flex align-items-center mb-0">
                            <input class="form-check-input" type="checkbox" name="subdirectories" id="add_folder_subdirectories" data-label-on="Include subdirectories" data-label-off="Exclude subdirectories" checked>
                            <label class="form-check-label ms-2 mb-0 fst-italic" for="add_folder_subdirectories">Exclude subdirectories</label>
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