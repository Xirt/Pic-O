<div class="offcanvas offcanvas-bottom offcanvas-bottom-sm" tabindex="-1" id="offcanvas-create-album-from-folder" aria-labelledby="offcanvasCreateAlbumFromFolderLabel">

    <div class="offcanvas-inner bg-white rounded-top mx-auto w-100 px-3">

        <div class="offcanvas-header">

            <h5 class="offcanvas-title" id="offcanvasCreateAlbumFromFolderLabel">
                Create album from folder
            </h5>

        </div>

        <div class="offcanvas-body small overflow-visible">

            <form action="{{ route('api.albums.storeFromFolder') }}" method="POST" id="createAlbumFromFolderForm" class="w-100">
            @csrf

                <div class="mb-1 row align-items-center">

                    <label for="album-name" class="col-sm-4 col-form-label">Album Title</label>

                    <div class="col-sm-8">

                        <div class="input-group input-group-sm">
                            <span class="input-group-text">
                                <i class="bi bi-fonts"></i>
                            </span>
                            <input type="text" class="form-control" id="album-name" name="name" placeholder="Album title" value="" required>
                        </div>

                    </div>

                </div>

                <div class="mb-1 row align-items-center">

                    <label for="album-type" class="col-sm-4 col-form-label">Type</label>

                    <div class="col-sm-8">

                        <div class="input-group input-group-sm">
                            <span class="input-group-text">
                                <i class="bi bi-list"></i>
                            </span>
                            <select class="form-select" id="album-type" name="type">
                                @foreach (\App\Enums\AlbumType::cases() as $type)
                                    <option value="{{ $type->value }}">
                                        {{ ucfirst($type->value) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                </div>

                <div class="mb-1 row align-items-center">

                    <label for="album-folder" class="col-sm-4 col-form-label">Folder</label>

                    <div class="col-sm-8">

                        <div class="position-relative m-0" data-tpl-option="folderOption" data-tpl-empty="noFolderOption" id="createAlbumFromFolder">

                            <div class="input-group input-group-sm">
                                <span class="input-group-text">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" class="form-control" id="album-folder" placeholder="Select a folder" readonly>
                                <input type="hidden" class="form-control" id="type" name="folder_id">
                            </div>

                            <div class="search-select-wrapper position-absolute bottom-100 start-0 w-100">

                                <div class="d-flex flex-column bg-white border rounded-2">

                                    <ul class="search-select-list list-group flex-fill overflow-auto border-bottom rounded-0 w-100" id="dropdownMenu"></ul>

                                    <input type="text" class="form-control form-control-sm flex-grow-1 w-auto m-2" placeholder="Search..." id="dropdownInput" aria-expanded="false" autocomplete="off" required />

                                </div>

                            </div>

                            <template id="folderOption">
                            <li class="list-group-item list-group-item-light list-group-item-action">
                                <a href="#" class="dropdown-item py-1">
                                <div class="form-select-option text-truncate">
                                    <b data-field='name'></b><br/>
                                    <span class="fw-light" data-field='path'></span>
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

                <div class="mb-1 mt-2 mt-sm-0 row align-items-center">

                    <label class="col-sm-4 col-form-label d-none d-sm-block">Scope</label>

                    <div class="col-sm-8 d-flex align-items-center">
                        <div class="form-check form-switch d-flex align-items-center mb-0">
                            <input class="form-check-input" type="checkbox" name="subdirectories" id="subDirSwitch" data-label-on="Include subdirectories" data-label-off="Exclude subdirectories" checked>
                            <label class="form-check-label ms-2 mb-0 fst-italic" for="subDirSwitch">Exclude subdirectories</label>
                        </div>
                    </div>

                </div>

                <div class="my-3 alert form-message alert-success">
                    The album has been successfully created.
                </div>

                <hr />

                <div class="d-flex justify-content-center mt-4 mb-3">

                    <button type="submit" class="btn btn-sm btn-primary form-processor w-50 mx-2 mx-sm-4">
                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                        <i class="bi bi-stars me-1"></i> Create
                    </button>

                    <button type="button" class="btn btn-sm btn-secondary w-50 mx-2 mx-sm-4" data-bs-dismiss="offcanvas">
                        <i class="bi bi-x-lg me-1"></i> Cancel
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>