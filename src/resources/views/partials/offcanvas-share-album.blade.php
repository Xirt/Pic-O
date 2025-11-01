<div class="offcanvas offcanvas-bottom offcanvas-bottom-sm" tabindex="-1" id="offcanvasShareAlbum" aria-labelledby="offcanvasShareLabel">

    <div class="offcanvas-inner bg-white rounded-top mx-auto w-100 px-3">

        <div class="offcanvas-header d-flex align-items-center justify-content-between mb-2 ms-1">

            <h5 class="offcanvas-title" id="offcanvasCreateLabel">
                Share album
            </h5>

            <div class="text-end">
                <button type="button" class="btn btn-sm btn-primary" id="generateTokenBtn" data-album-id="{{ $album->id }}">
                    <i class="bi bi-link-45deg"></i> Generate Link
                </button>
            </div>

        </div>

        <div class="d-flex offcanvas-body pt-0 small overflow-visible">

            <form action="{{ route('api.tokens.store') }}" id="shareTokenForm" class="w-100">
            @csrf

                <div id="tokenList" class="token-container d-grid gap-3 overflow-scroll">


                    <div class="no-token rounded p-3 bg-light">
                        These are no sharing links yet.
                    </div>


                    <template id="tokenTpl">
                    <div class="token-wrapper rounded p-3 bg-light">

                        <div class="text-end text-end fst-italic mb-1 expiry">
                            Validity: <span class="expires_at"></span>
                        </div>

                        <div class="d-flex">

                            <input class="form-control form-control-sm">

                            <button type="button" class="btn btn-sm btn-primary ms-2 btn-copy"><i class="bi bi-copy"></i></button>

                            <button type="button" class="btn btn-sm btn-secondary ms-2 btn-calendar d-none"><i class="bi bi-calendar3-event"></i></button>

                            <button type="button" class="btn btn-sm btn-secondary ms-2 btn-delete"><i class="bi bi-trash3"></i></button>

                        </div>

                    </div>
                    </template>

                </div>

                <hr class="my-4" />

                <div class="d-flex justify-content-center mb-3">

                    <button type="button" class="btn btn-sm btn-secondary w-50 mx-4" data-bs-dismiss="offcanvas">
                        <i class="bi bi-x-lg me-1"></i> Close
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>