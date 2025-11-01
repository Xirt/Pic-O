<div class="offcanvas offcanvas-bottom offcanvas-bottom-sm" tabindex="-1" id="offcanvas-create-album" aria-labelledby="offcanvasBottomLabel">

    <div class="offcanvas-inner bg-white rounded-top mx-auto w-100 px-3">

        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasBottomLabel">Create album</h5>
        </div>

        <div class="offcanvas-body small overflow-visible">

            <form action="{{ route('api.albums.store') }}" method="POST" id="createAlbumForm" class="w-100">
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

                    <label class="col-sm-4 col-form-label">Selection</label>

                    <div class="col-sm-8">

                        <div class="input-group input-group-sm">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <div class="form-control">
                                <span class="picture-count">0</span> picture(s)
                            </div>
                        </div>

                    </div>

                </div>

                <div class="my-3 alert form-message alert-success">
                    The album has been successfully created.
                </div>

                <hr />

                <div class="d-flex justify-content-center mt-4 mb-3">

                    <div class="d-none container-hidden"></div>

                    <button type="submit" class="btn btn-sm btn-primary w-50 mx-2 mx-sm-4">
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