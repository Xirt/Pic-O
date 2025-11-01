<div class="offcanvas offcanvas-bottom offcanvas-bottom-sm" tabindex="-1" id="offcanvasUpdateAlbum" aria-labelledby="offcanvasUpdateLabel">

    <div class="offcanvas-inner bg-white rounded-top mx-auto w-100 px-3">

        <div class="offcanvas-header">

            <h5 class="offcanvas-title" id="offcanvasUpdateLabel">
                Modify album
            </h5>

        </div>

        <div class="offcanvas-body small overflow-visible">

            <form action="" method="POST" id="updateAlbumForm" class="w-100">
            @csrf

                <div class="mb-1 row align-items-center">

                    <label for="album-name-2" class="col-sm-4 col-form-label">Album Title</label>

                    <div class="col-sm-8">

                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-fonts"></i>
                            </span>
                            <input type="text" class="form-control" id="album-name-2" name="name" placeholder="Album title" value="" required>
                        </div>

                    </div>

                </div>

                <div class="mb-1 row align-items-center">

                    <label for="album-type-2" class="col-sm-4 col-form-label">Type</label>

                    <div class="col-sm-8">

                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-list"></i>
                            </span>
                            <select class="form-select" id="album-type-2" name="type">
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

                    <label for="album-start-date" class="col-sm-4 col-form-label">Start Date</label>

                    <div class="col-sm-8">

                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-calendar"></i>
                            </span>
                            <input type="date" class="form-control" id="album-start-date" name="start_date" value="">
                        </div>

                    </div>

                </div>

                <div class="mb-1 row align-items-center">

                    <label for="album-end-date" class="col-sm-4 col-form-label">End Date</label>

                    <div class="col-sm-8">

                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-calendar"></i>
                            </span>
                            <input type="date" class="form-control" id="album-end-date" name="end_date" value="">
                        </div>

                    </div>

                </div>

                <div class="mb-1 row align-items-center">

                    <label for="album-date-precision" class="col-sm-4 col-form-label">Date Precision</label>

                    <div class="col-sm-8">

                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-list"></i>
                            </span>
                            <select class="form-select" id="album-date-precision" name="date_precision">
                                @foreach (\App\Enums\DatePrecision::cases() as $precision)
                                    <option value="{{ $precision->value }}">
                                        {{ ucfirst($precision->value) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                </div>

                <hr />

                <div class="d-flex justify-content-center mt-4 mb-3">

                    <input type="hidden" class="form-control" id="albumId" name="id" placeholder="Album title" value="">

                    <button type="submit" class="btn btn-primary w-50 mx-2 mx-sm-4">
                        <i class="bi bi-save me-1"></i> Save
                    </button>

                    <button type="button" class="btn btn-secondary w-50 mx-2 mx-sm-4" data-bs-dismiss="offcanvas">
                        <i class="bi bi-x-lg me-1"></i> Cancel
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>