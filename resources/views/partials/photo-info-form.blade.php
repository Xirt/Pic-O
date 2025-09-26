<div class="offcanvas offcanvas-bottom offcanvas-bottom-sm" tabindex="-1" id="offcanvas-info" aria-labelledby="offcanvasInfoLabel">

    <div class="offcanvas-inner bg-white rounded-top mx-auto w-100 px-3">

        <form action="#" method="GET" id="infoForm" class="w-100">
        @csrf

            <div class="offcanvas-body small overflow-visible">

                <input type="text" class="form-control-plaintext h5 fw-semibold text-center" id="filename" name="filename" value="">

                <hr />

                <div class="my-1 row align-items-center">

                    <label for="dimensions" class="col-4 col-form-label">Dimensions</label>

                    <div class="col-8">
                        <input type="text" class="form-control-plaintext" id="dimensions" name="dimensions" value="">
                    </div>

                </div>

                <div class="mb-1 row align-items-center">

                    <label for="taken_day_desc" class="col-4 col-form-label">Taken</label>

                    <div class="col-8">
                        <input type="text" class="form-control-plaintext" id="taken_day_desc" name="taken_day_desc" value="">
                    </div>

                </div>

                <div class="mb-1 row align-items-center">

                    <label for="aperture" class="col-4 col-form-label">Aperture</label>

                    <div class="col-8">
                        <input type="text" class="form-control-plaintext" id="aperture" name="aperture" value="">
                    </div>

                </div>

                <div class="mb-1 row align-items-center">

                    <label for="iso" class="col-4 col-form-label">ISO</label>

                    <div class="col-8">
                        <input type="text" class="form-control-plaintext" id="iso" name="iso" value="">
                    </div>

                </div>

                <div class="mb-1 row align-items-center">

                    <label for="focal_length" class="col-4 col-form-label">Focal length</label>

                    <div class="col-8">
                        <input type="text" class="form-control-plaintext" id="focal_length" name="focal_length" value="">
                    </div>

                </div>

                <div class="mb-1 row align-items-center">

                    <label for="exposure_time" class="col-4 col-form-label">Exposure time</label>

                    <div class="col-8">
                        <input type="text" class="form-control-plaintext" id="exposure_time" name="exposure_time" value="">
                    </div>

                </div>

                <hr />

                <div class="d-flex justify-content-center mt-4 mb-3">

                    <button type="button" class="btn btn-secondary w-50 mx-4" data-bs-dismiss="offcanvas">
                        <i class="bi bi-x-lg me-1"></i> Close
                    </button>

                </div>

            </div>

        </form>

    </div>

</div>