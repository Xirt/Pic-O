<div class="row g-3 g-lg-4 mx-lg-2">

    <div class="col-md-3 col-sm-6">

        <div class="card text-center shadow-sm border-0">

            <div class="card-body m-2">

                <i class="bi bi-folder display-2 text-warning"></i>

                <h3 class="text-secondary-emphasis mt-2 mb-0 fw-semibold">
                    {{ number_format($statistics['folders']) }}
                </h3>

                <small class="text-body-tertiary fs-5">
                    folders
                </small>

            </div>

        </div>

    </div>

    <div class="col-md-3 col-sm-6">

        <div class="card text-center shadow-sm border-0">

            <div class="card-body m-2">

                <i class="bi bi-collection display-2 text-success"></i>

                <h3 class="text-secondary-emphasis mt-2 mb-0 fw-semibold">
                    {{ number_format($statistics['albums']) }}
                </h3>

                <small class="text-body-tertiary fs-5">
                    albums
                </small>

            </div>

        </div>

    </div>

    <div class="col-md-3 col-sm-6">

        <div class="card text-center shadow-sm border-0">

            <div class="card-body m-2">

                <i class="bi bi-images display-2 text-info"></i>

                <h3 class="text-secondary-emphasis mt-2 mb-0 fw-semibold">
                    {{ number_format($statistics['photos']) }}
                </h3>

                <small class="text-body-tertiary fs-5">
                    photos
                </small>

            </div>

        </div>

    </div>

    <div class="col-md-3 col-sm-6">

        <div class="card text-center shadow-sm border-0">

            <div class="card-body m-2">

            <i class="bi bi-people display-2 text-primary"></i>

            <h3 class="text-secondary-emphasis mt-2 mb-0 fw-semibold">
                {{ number_format($statistics['users']) }}
            </h3>

            <small class="text-body-tertiary fs-5">
                users
            </small>

            </div>

        </div>

    </div>

    <div class="col-md-3 col-sm-6">

        <div class="card text-center shadow-sm border-0">

            <div class="card-body m-2">

                <i class="bi bi-eye display-2 text-danger"></i>

                <h3 class="text-secondary-emphasis mt-2 mb-0 fw-semibold">
                    {{ number_format($statistics['album_impressions']) }}
                </h3>

                <small class="text-body-tertiary fs-5">
                    album views
                </small>

            </div>

        </div>

    </div>

    <div class="col-md-3 col-sm-6">

        <div class="card text-center shadow-sm border-0">

            <div class="card-body m-2">

                <i class="bi bi-eye-fill display-2 text-purple"></i>

                <h3 class="text-secondary-emphasis mt-2 mb-0 fw-semibold">
                    {{ number_format($statistics['photo_impressions']) }}
                </h3>

                <small class="text-body-tertiary fs-5">
                    photo views
                </small>

            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">

        <div class="card text-center shadow-sm border-0">

            <div class="card-body m-2">

                <i class="bi bi-download display-2 text-secondary"></i>

                <h3 class="text-secondary-emphasis mt-2 mb-0 fw-semibold">
                    {{ number_format($statistics['photo_downloads']) }}
                </h3>

                <small class="text-body-tertiary fs-5">
                    downloads
                </small>

            </div>

        </div>

    </div>

</div>
